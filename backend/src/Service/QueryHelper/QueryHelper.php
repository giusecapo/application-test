<?php

declare(strict_types=1);

namespace App\Service\QueryHelper;

use App\Contract\Document\ConcurrencySafeDocumentInterface;
use App\Contract\Document\DocumentInterface;
use App\Contract\Document\FileDataInterface;
use App\Contract\Document\RelationalDocumentInterface;
use \DomainException as DomainException;
use App\Service\QueryHelper\SubsetQueryResult;
use \RuntimeException as RuntimeException;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Service\QueryHelper\QueryCriteria;
use App\Service\QueryHelper\SortingDescriptor;
use \Exception as Exception;
use App\Service\QueryHelper\CursorSubsetDescriptor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\ODM\MongoDB\LockException as DoctrineLockException;
use App\Service\Constant\ExceptionCodes;
use App\Utility\ArrayValidator;
use \DateTime as DateTime;
use MongoDB\Driver\Exception\BulkWriteException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Doctrine\ODM\MongoDB\Query\Expr;
use \InvalidArgumentException as InvalidArgumentException;
use MongoDB\BSON\Decimal128Interface;
use MongoDB\BSON\Int64;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use function count;
use function in_array;
use function is_int;
use function is_float;
use function is_array;
use function array_key_exists;

final class QueryHelper
{
    private const QUERY_OPTIONS = [
        'allowDiskUse' => true,
        //'collation' => ['locale' => 'en', 'strength' => 3],
    ];

    private const QUERY_TYPE_GET_SINGLE_RESULT = 0;
    private const QUERY_TYPE_FIND = 1;
    private const QUERY_TYPE_GET_SUBSET = 2;
    private const QUERY_TYPE_COUNT = 3;
    private const QUERY_TYPE_DISTINCT = 4;
    private const QUERY_TYPE_SUM = 5;
    private const QUERY_TYPE_HAS_PREVIOUS_PAGE = 6;
    private const QUERY_TYPE_HAS_NEXT_PAGE = 7;

    public function __construct(
        private DocumentManager $documentManager,
        private Cache $cache
    ) {
    }


    /**
     * Schedules a document for creation or update.
     * The document is persisted to the database only when the flush operation is invoked.
     */
    public function persist(DocumentInterface $document): void
    {
        $this->documentManager->persist($document);
        $this->cache->scheduleForInvalidation($document::class);
    }

    /**
     * Schedules a document for removal.
     * The document is removed from the database only when the flush operation is invoked.
     */
    public function remove(DocumentInterface $document): void
    {
        $this->documentManager->remove($document);
        $this->cache->scheduleForInvalidation($document::class);
    }


    /**
     * Executes all the operations registered in the unit of work.
     */
    public function flush(array $options = [], bool $useTransaction = false): void
    {
        $session = null;
        if ($useTransaction) {
            $session = $this->documentManager->getClient()->startSession();
            $options['session'] = $session;
        }

        try {
            if ($useTransaction) {
                $session->startTransaction();
            }

            $this->documentManager->flush($options);
            $this->cache->invalidateCache();

            if ($useTransaction) {
                $session->commitTransaction();
                $session->endSession();
            }
        } catch (Exception $exception) {

            if ($useTransaction) {
                $session->abortTransaction();
                $session->endSession();
            }

            //Lock exception
            if ($exception instanceof DoctrineLockException) {
                throw new ConflictHttpException(
                    $exception->getMessage(),
                    $exception,
                    ExceptionCodes::LOCK_EXCEPTION
                );
            }

            //MongoDB Driver exceptions
            if ($exception instanceof BulkWriteException) {
                switch ($exception->getCode()) {
                    case 11000: //Unique index violation   
                        throw new PreconditionFailedHttpException(
                            'An element with the provided value already exists in the system',
                            $exception,
                            ExceptionCodes::DUPLICATE_EXCEPTION
                        );
                        break;
                }
            }

            //General runtime exception / unknown error
            throw new RuntimeException(
                sprintf('Failed at persisting the document(s). Original error message: %s', $exception->getMessage()),
                ExceptionCodes::RUNTIME_EXCEPTION,
                $exception
            );
        }
    }

    /**
     * Clears the unit of work.
     */
    public function clear(): void
    {
        $this->documentManager->clear();
    }

    /**
     * Apply optimistic or pessimistic locking to a document.
     * @throws ConflictHttpException if the document is locked
     */
    public function lock(ConcurrencySafeDocumentInterface $document, int $lockMode, ?int $expectedVersion): void
    {
        try {
            $this->documentManager->lock($document, $lockMode, $expectedVersion);
        } catch (DoctrineLockException $exception) {
            throw new ConflictHttpException($exception->getMessage(), $exception, ExceptionCodes::LOCK_EXCEPTION);
        }
    }


    /**
     * Disable a query filter (ODM query filter)
     */
    public function disableFilter(string $filterName): void
    {
        $this->documentManager->getFilterCollection()->disable($filterName);
    }

    /**
     * Enable a query filter (ODM query filter)
     */
    public function enableFilter(string $filterName): void
    {
        $this->documentManager->getFilterCollection()->enable($filterName);
    }

    // ==== UPDATE QUERY =============================
    /**
     * Update the first document matching the given query criteria
     * with an update query without passing through the unit of work.
     */
    public function updateOneNow(string $documentName, QueryCriteria $queryCriteria): void
    {
        $this->cache->invalidate($documentName);
        $queryBuilder = $this->documentManager
            ->createQueryBuilder($documentName)
            ->updateOne();
        $this->filter($documentName, $queryBuilder, $queryCriteria);
        $this->update($queryBuilder, $queryCriteria);
        $queryBuilder->getQuery()->execute();
    }

    /**
     * Update all documents matching the given query criteria
     * with an update query without passing through the unit of work.
     */
    public function updateManyNow(string $documentName, QueryCriteria $queryCriteria): void
    {
        $this->cache->invalidate($documentName);
        $queryBuilder = $this->documentManager
            ->createQueryBuilder($documentName)
            ->updateMany();
        $this->filter($documentName, $queryBuilder, $queryCriteria);
        $this->update($queryBuilder, $queryCriteria);
        $queryBuilder->getQuery()->execute();
    }

    // ==== DELETE QUERY =============================

    /**
     * Delete the documents matching the given query criteria
     * with a delete query without passing through the unit of work.
     */
    public function deleteNow(string $documentName, QueryCriteria $queryCriteria): void
    {
        $this->cache->invalidate($documentName);
        $queryBuilder = $this->documentManager
            ->createQueryBuilder($documentName)
            ->remove();
        $this->filter($documentName, $queryBuilder, $queryCriteria);
        $queryBuilder->getQuery()->execute();
    }

    // ==== GET SINGLE RESULT =============================

    /**
     * Get the first document matching the given query criteria
     */
    public function getSingleResult(string $documentName, QueryCriteria $queryCriteria): ?DocumentInterface
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_GET_SINGLE_RESULT,
            $queryCriteria->isReadOnly(),
            function () use ($documentName, $queryCriteria): null|array|DocumentInterface {
                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->readOnly($queryBuilder, $queryCriteria);
                $this->selectFields($queryBuilder, $queryCriteria);
                $this->primeFields($queryBuilder, $queryCriteria);
                $this->filter($documentName, $queryBuilder, $queryCriteria);
                $this->offsetSortAndSlice($queryBuilder, $queryCriteria);
                return $queryBuilder->hydrate(!$queryCriteria->isReadOnly())->getQuery(self::QUERY_OPTIONS)->getSingleResult();
            },
            fn (?array $notHydratedDocumentData) => $this->hydrateDocument($documentName, $notHydratedDocumentData)
        );
    }

    // ==== GET SUBSET =============================

    /**
     * Get a paginated subset of documents from a collection.
     * The pagination strategy is defined by the QueryCriteria object.
     */
    public function getSubset(string $documentName, QueryCriteria $queryCriteria): SubsetQueryResult
    {
        if ($queryCriteria->getQueryStrategy() == QueryCriteria::QUERY_STRATEGY_CURSOR) {
            return $this->cursorGetSubset($documentName, $queryCriteria);
        }
        return $this->offsetGetSubset($documentName, $queryCriteria);
    }

    /**
     * Get a subset of the documents stored in a collection using cursor based pagination strategy
     */
    private function cursorGetSubset(string $documentName, QueryCriteria $queryCriteria): SubsetQueryResult
    {
        /**
         * Forward pagination schema:
         *           
         *   1  2 [ 3  4  5 ] 6  7  8  9  
         *      |
         *  after cursor
         * 
         * The numbers represent our data set, sorted by Id ASC
         * Documents between [] are the result of the base query.
         * They are are stored in the SubsetQueryResult object in the prop '$documents'
         * 
         * All the records are sorted like in the given example.
         *  $documents                 -> 3,4,5
         * 
         * Backward pagination schema:
         * 
         *   1  2 [ 3  4  5 ] 6  7  8  9  
         *                    |
         *               before cursor
         * 
         * The numbers represent our data set, sorted by Id ASC
         * Documents between [] are the result of the query.
         * 
         * All the records are sorted like given in the given example.
         *  $documents                 -> 3,4,5
         */
        $subsetQueryResult = new SubsetQueryResult();
        $subsetQueryResult
            ->setDocuments($this->cursorGetDocumentsSubset($documentName, $queryCriteria))
            ->setDocumentsTotalCount(
                $queryCriteria->getTotalCount()
                    ? $this->getDocumentsTotalCount($documentName, $queryCriteria)
                    : null
            )
            ->setHasPreviousPage($this->cursorGetHasPreviousPage($documentName, $queryCriteria))
            ->setHasNextPage($this->cursorGetHasNextPage($documentName, $queryCriteria))
            ->setQueryCriteria($queryCriteria);

        return $subsetQueryResult;
    }

    /**
     * Get a subset of the documents stored in a collection using limit/offset based pagination strategy
     */
    private function offsetGetSubset(string $documentName, QueryCriteria $queryCriteria): SubsetQueryResult
    {
        if ($queryCriteria->getOffsetAndLimitDescriptor() === null) {
            throw new DomainException(
                'The OffsetAndLimitDescriptor is not set.',
                ExceptionCodes::DOMAIN_EXCEPTION
            );
        }
        $subsetQueryResult = new SubsetQueryResult();
        $subsetQueryResult
            ->setDocuments($this->offsetGetDocumentsSubset($documentName, $queryCriteria))
            ->setDocumentsTotalCount(
                $queryCriteria->getTotalCount()
                    ? $this->getDocumentsTotalCount($documentName, $queryCriteria)
                    : null
            )
            ->setHasPreviousPage(($queryCriteria->getOffsetAndLimitDescriptor()?->getOffset() ?? 0) > 0)
            ->setHasNextPage($this->offsetGetHasNextPage($documentName, $queryCriteria))
            ->setQueryCriteria($queryCriteria);

        return $subsetQueryResult;
    }

    // ==== FIND =============================

    /**
     * Find all documents matching the given query criteria
     */
    public function find(string $documentName, QueryCriteria $queryCriteria): Collection
    {
        if ($queryCriteria->getQueryStrategy() == QueryCriteria::QUERY_STRATEGY_CURSOR) {
            return $this->cursorFind($documentName, $queryCriteria);
        }
        return $this->offsetFind($documentName, $queryCriteria);
    }

    /**
     * Find all documents matching the given query criteria using cursor based pagination strategy
     */
    private function cursorFind(string $documentName, QueryCriteria $queryCriteria): Collection
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_FIND,
            $queryCriteria->isReadOnly(),
            function () use ($documentName, $queryCriteria): array|Collection {
                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->readOnly($queryBuilder, $queryCriteria);
                $this->selectFields($queryBuilder, $queryCriteria);
                $this->primeFields($queryBuilder, $queryCriteria);
                $this->filter($documentName, $queryBuilder, $queryCriteria);
                $this->cursorSortAndSlice($queryBuilder, $queryCriteria);
                $result = $queryBuilder->hydrate(!$queryCriteria->isReadOnly())->getQuery(self::QUERY_OPTIONS)->execute()->toArray();
                return $queryCriteria->isReadOnly()
                    ? $result
                    : new ArrayCollection($result);
            },
            fn ($notHydratedDocumentsData) => $this->hydrateDocuments($documentName, $notHydratedDocumentsData)
        );
    }

    /**
     * Find all documents matching the given query criteria using offset - limit based pagination strategy
     */
    private function offsetFind(string $documentName, QueryCriteria $queryCriteria): Collection
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_FIND,
            $queryCriteria->isReadOnly(),
            function () use ($documentName, $queryCriteria): array|Collection {
                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->readOnly($queryBuilder, $queryCriteria);
                $this->selectFields($queryBuilder, $queryCriteria);
                $this->primeFields($queryBuilder, $queryCriteria);
                $this->filter($documentName, $queryBuilder, $queryCriteria);
                $this->offsetSortAndSlice($queryBuilder, $queryCriteria);
                $result = $queryBuilder->hydrate(!$queryCriteria->isReadOnly())->getQuery(self::QUERY_OPTIONS)->execute()->toArray();
                return $queryCriteria->isReadOnly()
                    ? $result
                    : new ArrayCollection($result);
            },
            fn ($notHydratedDocumentsData) => $this->hydrateDocuments($documentName, $notHydratedDocumentsData)
        );
    }


    // ==== COUNT =============================

    public function count(string $documentName, QueryCriteria $queryCriteria): int
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_COUNT,
            true,
            function () use ($documentName, $queryCriteria): int {
                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->filter($documentName, $queryBuilder, $queryCriteria);
                $this->getCount($queryBuilder);
                return $queryBuilder->hydrate(false)->getQuery(self::QUERY_OPTIONS)->execute();
            }
        );
    }

    // ==== DISTINCT =============================

    /**
     * Get a list of distinct field values for the given field of all documents matching the given query
     */
    public function distinct(string $documentName, QueryCriteria $queryCriteria): Collection
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_DISTINCT,
            true,
            function () use ($documentName, $queryCriteria): Collection {
                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->filter($documentName, $queryBuilder, $queryCriteria);
                $this->getDistinct($queryBuilder, $queryCriteria);
                return new ArrayCollection($queryBuilder->getQuery(self::QUERY_OPTIONS)->execute());
            }
        );
    }

    // ==== SUM =============================

    /**
     * Sum the values of the given field of all documents matching the given query
     */
    public function sum(string $documentName, QueryCriteria $queryCriteria): float|int
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_SUM,
            true,
            function () use ($documentName, $queryCriteria): int|float {
                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->filter($documentName, $queryBuilder, $queryCriteria);
                if ($queryCriteria->getQueryStrategy() == QueryCriteria::QUERY_STRATEGY_CURSOR) {
                    $this->cursorSortAndSlice($queryBuilder, $queryCriteria);
                } else {
                    $this->offsetSortAndSlice($queryBuilder, $queryCriteria);
                }
                $queryResults = $queryBuilder
                    ->hydrate(false)
                    ->select($queryCriteria->getSumField())
                    ->getQuery(self::QUERY_OPTIONS)
                    ->execute()
                    ->toArray();
                $sumFieldPath = explode('.', $queryCriteria->getSumField());

                $sum = 0;
                foreach ($queryResults as $queryResult) {
                    foreach ($sumFieldPath as $slug) {
                        $queryResult = $queryResult[$slug] ?? null;
                        if (is_array($queryResult) && !ArrayValidator::isAssoc($queryResult)) {
                            throw new DomainException('Cannot sum on array fields', ExceptionCodes::DOMAIN_EXCEPTION);
                        }
                    }

                    if (
                        $queryResult instanceof Decimal128Interface
                        || $queryResult instanceof Int64
                    ) {
                        $queryResult = (float)$queryResult->__toString();
                    }

                    if (!is_int($queryResult) && !is_float($queryResult)) {
                        throw new DomainException(
                            'Cannot sum fields because one or more values are not numbers',
                            ExceptionCodes::DOMAIN_EXCEPTION
                        );
                    }

                    $sum += $queryResult;
                }

                return $sum;
            }
        );
    }

    // ==== UPLOAD / DOWNLOAD =============================

    /**
     * Uploads data to persistent storage
     */
    public function upload(string $documentName, string $basename, string $data): FileDataInterface
    {
        $uploadStream = fopen('php://temp', 'w+b');
        fwrite($uploadStream, $data);
        rewind($uploadStream);
        return $this->documentManager->getRepository($documentName)->uploadFromStream($basename, $uploadStream);
    }

    /**
     * Reads data from persistent storage
     */
    public function download(string $documentName, string $id): string
    {
        $downloadStream = $this->documentManager->getRepository($documentName)->openDownloadStream($id);
        $download = stream_get_contents($downloadStream);
        return is_string($download) ? $download : '';
    }

    // ============================================= //
    // HELPER METHODS
    // ============================================= //

    // ==== UPDATE QUERIES HELPER METHODS =============================
    /**
     * Apply the update operations defined in the QueryCriteria's UpdateDescriptor to the query builder
     */
    private function update(QueryBuilder $queryBuilder, QueryCriteria $queryCriteria): void
    {
        if (!$queryCriteria->getUpdateDescriptor() === null) {
            throw new InvalidArgumentException(
                'No UpdateDescriptor was provided to the update query.',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        foreach ($queryCriteria->getUpdateDescriptor()->getUpdateOperationsAsArray() as $updateOperation) {
            $value = $this->parseUpdateValue($updateOperation['value']);
            $operator = $updateOperation['operator'];
            $field = $updateOperation['field'];

            $queryBuilder->field($field)->$operator($value);
        }
    }

    private function parseUpdateValue(mixed $value): mixed
    {
        if ($value instanceof DateTime) {
            return new UTCDateTime($value->format('U') * 1000);
        }

        return $value;
    }

    // ==== OFFSET QUERIES HELPER METHODS =============================

    /**
     * Build and perform the document subset query using offset - limit based pagination strategy
     */
    private function offsetGetDocumentsSubset(string $documentName, QueryCriteria $queryCriteria): Collection
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_GET_SUBSET,
            $queryCriteria->isReadOnly(),
            function () use ($documentName, $queryCriteria): array|Collection {
                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->readOnly($queryBuilder, $queryCriteria);
                $this->selectFields($queryBuilder, $queryCriteria);
                $this->primeFields($queryBuilder, $queryCriteria);
                $this->filter($documentName, $queryBuilder, $queryCriteria);
                $this->offsetSortAndSlice($queryBuilder, $queryCriteria);
                $result = $queryBuilder->hydrate(!$queryCriteria->isReadOnly())->getQuery(self::QUERY_OPTIONS)->execute()->toArray();
                return $queryCriteria->isReadOnly()
                    ? $result
                    : new ArrayCollection($result);
            },
            fn ($notHydratedDocumentsData) => $this->hydrateDocuments($documentName, $notHydratedDocumentsData)
        );
    }


    /**
     * Apply sort and slice query parameters to a offset - limit subset query
     */
    private function offsetSortAndSlice(QueryBuilder $queryBuilder, QueryCriteria $queryCriteria): void
    {
        if ($queryCriteria->getSortingDescriptor() !== null) {
            $sortBy = $queryCriteria->getSortingDescriptor()->getSortBy();
            $sortDirection = $queryCriteria->getSortingDescriptor()->getSortDirection();
            $queryBuilder->sort($sortBy, $sortDirection);
        }
        if (
            $queryCriteria->getOffsetAndLimitDescriptor() !== null
            && $queryCriteria->getOffsetAndLimitDescriptor()->getOffset() !== null
        ) {
            $offset = $queryCriteria->getOffsetAndLimitDescriptor()->getOffset();
            $queryBuilder->skip($offset);
        }

        if (
            $queryCriteria->getOffsetAndLimitDescriptor() !== null
            && $queryCriteria->getOffsetAndLimitDescriptor()->getLimit() !== null
        ) {
            $limit = $queryCriteria->getOffsetAndLimitDescriptor()->getLimit();
            $queryBuilder->limit($limit);
        }
    }

    // ==== CURSOR QUERIES HELPER METHODS =============================

    /**
     * Build and perform the document subset query using cursor based pagination strategy
     */
    private function cursorGetDocumentsSubset(string $documentName, QueryCriteria $queryCriteria): Collection
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_GET_SUBSET,
            $queryCriteria->isReadOnly(),
            function () use ($documentName, $queryCriteria): array|Collection {
                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->readOnly($queryBuilder, $queryCriteria);
                $this->selectFields($queryBuilder, $queryCriteria);
                $this->primeFields($queryBuilder, $queryCriteria);
                $this->filter($documentName, $queryBuilder, $queryCriteria);
                $this->cursorPaginate($queryBuilder, $queryCriteria);
                $this->cursorSortAndSlice($queryBuilder, $queryCriteria);
                $result = $queryBuilder->hydrate(!$queryCriteria->isReadOnly())->getQuery(self::QUERY_OPTIONS)->execute()->toArray();
                $result = $this->isPaginatingForward($queryCriteria) ? $result : array_reverse($result);
                return $queryCriteria->isReadOnly()
                    ? $result
                    : new ArrayCollection($result);
            },
            fn ($notHydratedDocumentsData) => $this->hydrateDocuments($documentName, $notHydratedDocumentsData)
        );
    }

    /**
     * Apply pagination parameters to a cursor based query
     */
    private function cursorPaginate(QueryBuilder $queryBuilder, QueryCriteria $queryCriteria): void
    {
        $sortBy = $queryCriteria->getSortingDescriptor()->getSortBy();
        $sortDirection = $queryCriteria->getSortingDescriptor()->getSortDirection();
        $after = $queryCriteria->getCursorSubsetDescriptor()->getAfter();
        $before = $queryCriteria->getCursorSubsetDescriptor()->getBefore();

        if ($this->isPaginatingForward($queryCriteria) && isset($after)) {
            //we are paginating forward and are on page 1 + n because a cursor is set
            $gtOrLt = $sortDirection === SortingDescriptor::ASC ? 'gt' : 'lt';
            $queryBuilder->addOr(
                $queryBuilder->expr()->field($sortBy)->$gtOrLt($after->getCursorValue()),
                $queryBuilder->expr()->field($sortBy)->equals($after->getCursorValue())->field('id')->$gtOrLt($after->getId())
            );
        } elseif (isset($before)) {
            //we are paginating backward and are on the last page - n because a cursor is set
            $gtOrLt = $sortDirection === SortingDescriptor::ASC ? 'lt' : 'gt';
            $queryBuilder->addOr(
                $queryBuilder->expr()->field($sortBy)->$gtOrLt($before->getCursorValue()),
                $queryBuilder->expr()->field($sortBy)->equals($before->getCursorValue())->field('id')->$gtOrLt($before->getId())
            );
        }
    }

    /**
     * Apply sort and slice parameters to a cursor based subset query
     */
    private function cursorSortAndSlice(QueryBuilder $queryBuilder, QueryCriteria $queryCriteria, int $skip = 0): void
    {
        $sortBy = $queryCriteria->getSortingDescriptor()->getSortBy();
        $sortDirection = $queryCriteria->getSortingDescriptor()->getSortDirection();
        $limit = $this->isPaginatingForward($queryCriteria)
            ? $queryCriteria->getCursorSubsetDescriptor()->getFirst()
            : $queryCriteria->getCursorSubsetDescriptor()->getLast();


        if ($this->isPaginatingForward($queryCriteria)) {
            //If $sortBy === 'id', the sort array will contain just one item,
            //which is the desired behavior.
            $queryBuilder
                ->sort([$sortBy => $sortDirection, 'id' => $sortDirection])
                ->skip($skip)
                ->limit($limit);
        } else {
            //we are paginating backward: sort direction must be inverted
            $invSortDirection = $sortDirection === SortingDescriptor::ASC
                ? SortingDescriptor::DESC
                : SortingDescriptor::ASC;
            //If $sortBy === 'id', the sort array will contain just one item,
            //which is the desired behavior.
            $queryBuilder
                ->sort([$sortBy => $invSortDirection, 'id' => $invSortDirection])
                ->skip($skip)
                ->limit($limit);
        }
    }

    /**
     * Build and perform a query to check if the data set of a cursor based pagination has a previous page 
     */
    private function cursorGetHasPreviousPage(string $documentName, QueryCriteria $queryCriteria): bool
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_HAS_PREVIOUS_PAGE,
            true,
            function () use ($documentName, $queryCriteria): bool {
                //To check if there are previous pages we have to revert the base query :
                //
                //If the base query is paginating forward, we must paginate backwards
                //If the base query is paginating backward, we must paginate forward
                //After and before cursors must be swapped
                //Also first and last values must be swapped and 
                //We also need to invert the sorting direction

                /**
                 * Forward pagination schema :
                 * The numbers represent our data set. Documents between [] are the result of the base query (records from 6 to 9)
                 * Here we are building a QueryCriteria object which checks if there are documents before the first result (6).
                 * 
                 *                    our start cursor
                 *                        |
                 *        1  2  3  4  5 [ 6  7  8  9 ]                        
                 *                        |-> paginating forward (main query)             
                 *   check this records <-| (paginating backward) (our query)  
                 */

                //If both before and after cursors are null
                //we are at the beginning or end of the set (first or last page)
                if (
                    $queryCriteria->getCursorSubsetDescriptor()->getBefore() === null
                    && $queryCriteria->getCursorSubsetDescriptor()->getAfter() === null
                ) {
                    return false;
                }

                $newCursorSubsetDescriptor = new CursorSubsetDescriptor();
                $newFirst = $queryCriteria->getCursorSubsetDescriptor()->getLast() != null
                    ? 1
                    : null;
                $newLast = $queryCriteria->getCursorSubsetDescriptor()->getFirst() != null
                    ? 1
                    : null;
                $newCursorSubsetDescriptor
                    ->setBefore($queryCriteria->getCursorSubsetDescriptor()->getAfter())
                    ->setAfter($queryCriteria->getCursorSubsetDescriptor()->getBefore())
                    ->setFirst($newFirst)
                    ->setLast($newLast);

                $newQueryCriteria = new QueryCriteria();
                $newQueryCriteria
                    ->setCursorSubsetDescriptor($newCursorSubsetDescriptor)
                    ->setFiltersDescriptor($queryCriteria->getFiltersDescriptor())
                    ->setSortingDescriptor($queryCriteria->getSortingDescriptor());

                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->filter($documentName, $queryBuilder, $newQueryCriteria);
                $this->cursorPaginate($queryBuilder, $newQueryCriteria);
                $this->cursorSortAndSlice($queryBuilder, $newQueryCriteria);
                $this->getCount($queryBuilder);
                return $queryBuilder->getQuery(self::QUERY_OPTIONS)->execute() > 0;
            }
        );
    }

    /**
     * Build and perform a query to check if the data set of a cursor based pagination has a next page 
     */
    private function cursorGetHasNextPage(string $documentName, QueryCriteria $queryCriteria): bool
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_HAS_NEXT_PAGE,
            true,
            function () use ($documentName, $queryCriteria): bool {
                /**
                 * Forward pagination schema:
                 * The numbers represent our data set. Documents between [] are the result of the base query (records from 2 to 5)
                 * Here we are building a QueryCriteria object which checks if there are documents after the last result (5).
                 * 
                 *      our start cursor
                 *            |
                 *        1  [2  3  4  5]   6  7  8  9                
                 *            |-> paginating forward  (main query)            
                 *            |-> skip      |-> take this records (paginating forward) (our query)           
                 */
                $newCursorSubsetDescriptor = new CursorSubsetDescriptor();
                $newFirst = $queryCriteria->getCursorSubsetDescriptor()->getFirst() !== null
                    ? 1
                    : null;
                $newLast = $queryCriteria->getCursorSubsetDescriptor()->getLast() !== null
                    ? 1
                    : null;
                $newCursorSubsetDescriptor
                    ->setBefore($queryCriteria->getCursorSubsetDescriptor()->getBefore())
                    ->setAfter($queryCriteria->getCursorSubsetDescriptor()->getAfter())
                    ->setFirst($newFirst)
                    ->setLast($newLast);

                $newQueryCriteria = new QueryCriteria();
                $newQueryCriteria
                    ->setCursorSubsetDescriptor($newCursorSubsetDescriptor)
                    ->setFiltersDescriptor($queryCriteria->getFiltersDescriptor())
                    ->setSortingDescriptor($queryCriteria->getSortingDescriptor());

                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->filter($documentName, $queryBuilder, $newQueryCriteria);
                $this->cursorPaginate($queryBuilder, $newQueryCriteria);
                //we have to skip the records included in the base query result
                $skip = $queryCriteria->getCursorSubsetDescriptor()->getFirst() ?? $queryCriteria->getCursorSubsetDescriptor()->getLast();
                $this->cursorSortAndSlice($queryBuilder, $newQueryCriteria, $skip);
                $this->getCount($queryBuilder);
                return $queryBuilder->getQuery(self::QUERY_OPTIONS)->execute() > 0;
            }
        );
    }

    /**
     * Build and perform a query to check if the data set of a offset based pagination has a next page 
     */
    private function offsetGetHasNextPage(string $documentName, QueryCriteria $queryCriteria): bool
    {
        return $this->cache->get(
            $documentName,
            $queryCriteria,
            self::QUERY_TYPE_HAS_NEXT_PAGE,
            true,
            function () use ($documentName, $queryCriteria): bool {
                $newOffsetAndLimitDescriptor = new OffsetAndLimitDescriptor();
                $newOffsetAndLimitDescriptor
                    ->setOffset(
                        ($queryCriteria->getOffsetAndLimitDescriptor()->getOffset() ?? 0)
                            + $queryCriteria->getOffsetAndLimitDescriptor()->getLimit()
                    )
                    ->setLimit(1);

                $newQueryCriteria = new QueryCriteria();
                $newQueryCriteria
                    ->setOffsetAndLimitDescriptor($newOffsetAndLimitDescriptor)
                    ->setFiltersDescriptor($queryCriteria->getFiltersDescriptor())
                    ->setSortingDescriptor($queryCriteria->getSortingDescriptor());

                $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
                $this->filter($documentName, $queryBuilder, $newQueryCriteria);
                $this->offsetSortAndSlice($queryBuilder, $newQueryCriteria);
                $this->getCount($queryBuilder);
                return $queryBuilder->getQuery(self::QUERY_OPTIONS)->execute() > 0;
            }
        );
    }


    /**
     * Determine if the subset query we are going to build is for forward or backward pagination
     */
    private function isPaginatingForward(QueryCriteria $queryCriteria): bool
    {
        return $queryCriteria->getCursorSubsetDescriptor()->getFirst() !== null;
    }

    /**
     * Apply all the filters stored in the filters descriptor to the query
     */
    private function filter(string $documentName, QueryBuilder $queryBuilder, QueryCriteria $queryCriteria): void
    {
        if ($queryCriteria->getFiltersDescriptor() === null) {
            return;
        }

        try {
            $this->applyFilters($documentName, $queryBuilder, $queryBuilder, $queryCriteria->getFiltersDescriptor());
        } catch (Exception $exception) {
            throw new RuntimeException(
                'Cannot apply filters to query.',
                ExceptionCodes::RUNTIME_EXCEPTION,
                $exception
            );
        }
    }

    /**
     * Resolve and apply all the filters stored in the filters descriptor to the query
     */
    private function applyFilters(
        string $documentName,
        QueryBuilder $queryBuilder,
        QueryBuilder|Expr $queryBuilderOrExpr,
        FiltersDescriptor $filtersDescriptor
    ): void {
        $this->applyJoinFilters($documentName, $queryBuilder, $filtersDescriptor);
        $this->applySimpleFilters($documentName, $queryBuilder, $queryBuilderOrExpr, $filtersDescriptor);
    }

    private function applyJoinFilters(
        string $documentName,
        QueryBuilder $queryBuilder,
        FiltersDescriptor $filtersDescriptor
    ): void {
        // Join filters must be resolved and applied to the query before any other simple filter is applied to the query
        // because for mongodb this query {referenceField: ObjectId('123'), referenceField: {$in: [ObjectId('123'), ObjectId('456')]}}
        // is not the same as this {referenceField: {$in: [ObjectId('123'), ObjectId('456')], referenceField: ObjectId('123')}}
        $joinFiltersGroupedByJoinFilterField = array();
        foreach ($filtersDescriptor->getFiltersAsArray() as $filter) {
            if ($this->isJoinFilter($documentName, $filter['field'])) {
                $filterField = $this->getJoinFilterFieldName($documentName, $filter['field']);
                $joinFiltersGroupedByJoinFilterField[$filterField][] = $filter;
            }
        }

        //Each join query must be resolved and the resulting ids must be applied to the main query
        foreach ($joinFiltersGroupedByJoinFilterField as $filterField => $filters) {
            $referencedDocumentName = $this->getReferencedDocumentName($documentName, $filters[0]['field']);
            $joinQueryBuilder = $this->documentManager->createQueryBuilder($referencedDocumentName);
            foreach ($filters as $filter) {
                $this->applySimpleFilter(
                    $documentName,
                    $joinQueryBuilder,
                    [
                        'operator' => $filter['operator'],
                        'field' => $this->getJoinQueryFilterFieldName($documentName, $filter['field']),
                        'value' => $filter['value']
                    ]
                );
            }

            $joinIds = array_map(
                fn (array $joinResult) => $joinResult['_id'],
                $joinQueryBuilder->hydrate(false)->select(['_id'])->getQuery()->execute()->toArray()
            );

            $queryBuilder->field($filterField)->in($joinIds);
        }
    }


    private function applySimpleFilters(
        string $documentName,
        QueryBuilder $queryBuilder,
        QueryBuilder|Expr $queryBuilderOrExpr,
        FiltersDescriptor $filtersDescriptor
    ): void {

        foreach ($filtersDescriptor->getFiltersAsArray() as $filter) {
            if ($filter['operator'] === 'and') {
                $expr = $queryBuilder->expr();
                $this->applyFilters($documentName, $queryBuilder, $expr, $filter['value']);
                $queryBuilderOrExpr->addAnd(...[$expr]);
            } elseif ($filter['operator'] === 'or') {
                $expressions = array();
                foreach ($filter['value'] as $filtersDescriptor) {
                    if (count($filtersDescriptor->getFiltersAsArray()) > 0) {
                        $expr = $queryBuilder->expr();
                        $this->applyFilters($documentName, $queryBuilder, $expr, $filtersDescriptor);
                        $expressions[] = $expr;
                    }
                }
                $queryBuilderOrExpr->addOr(...$expressions);
            } elseif (!$this->isJoinFilter($documentName, $filter['field'])) {
                $this->applySimpleFilter($documentName, $queryBuilderOrExpr, $filter);
            }
        }
    }

    private function applySimpleFilter(string $documentName, QueryBuilder|Expr $queryBuilderOrExpr, array $filter): void
    {
        match ($filter['operator']) {
            'geoWithinPolygon' => $this->applyGeoWithinPolygonFilter($queryBuilderOrExpr, $filter),
            'geoWithinCenterSphere' => $this->applyGeoWithinCenterSphereFilter($queryBuilderOrExpr, $filter),
            default => $this->applyOtherFilter($documentName, $queryBuilderOrExpr, $filter),
        };
    }

    private function applyGeoWithinPolygonFilter(QueryBuilder|Expr $queryBuilderOrExpr, array $filter): void
    {
        $queryBuilderOrExpr->field($filter['field'])->geoWithinPolygon(...$filter['value']);
    }

    private function applyGeoWithinCenterSphereFilter(QueryBuilder|Expr $queryBuilderOrExpr, array $filter): void
    {
        $queryBuilderOrExpr->field($filter['field'])->geoWithinCenterSphere($filter['value'][0], $filter['value'][1], $filter['value'][2]);
    }

    private function applyOtherFilter(string $documentName, QueryBuilder|Expr $queryBuilderOrExpr, array $filter): void
    {
        $operator = $filter['operator'];
        $field = $filter['field'];
        $value = $filter['value'];

        if (isset($value) && is_string($value) && $this->isReferencedField($documentName, $field)) {
            $value = new ObjectId($value);
        }

        if (isset($value) && is_array($value) && $this->isReferencedField($documentName, $field)) {
            $value =  array_map(fn (string $id) => new ObjectId($id), $value);
        }

        $queryBuilderOrExpr->field($field)->$operator($value);
    }

    /**
     * Check if the filter is a join.
     * Join filters differ from simple filters in that they filter on fields of other documents
     * that are references of the current document type.
     * 
     * For example when querying the Estimate collection, a filter that has the field 'customer.firstName' is a join filter
     * because the Customer document only holds a reference to the User document in the field 'customer'
     * and the 'firstName' is stored instead in the User document.
     */
    private function isJoinFilter(string $documentName, ?string $filterField): bool
    {
        //filters in which the field is null are 'or' conditions
        if (!isset($filterField)) {
            return false;
        }

        //Two conditions must be met for a filter to be a join filter:
        // - the filter must be applied to a field of another document
        // - the document that the filter must be applied to, is referenced by the current document
        // This two conditions are met when the filter field begins with the name 
        // of a referenced field of the current document.
        // For example 'user.firstName' meets the conditions if the current document
        // has a referenced field 'user' that references the User document.
        $documentReferencesMap = $this->getDocumentReferencesMap($documentName);
        foreach (array_keys($documentReferencesMap) as $documentReferencedField) {
            if (
                $filterField !== $documentReferencedField
                && str_contains($filterField, '.')
                && str_starts_with($filterField, sprintf('%s.', $documentReferencedField))
            ) {
                return true;
            }
        }

        //Another way to apply join filters is to use the fully qualified name of the referenced document
        //in the filter field. For example 'user:App\Document\User.firstName' is a join filter if the current document
        //contains a field named user that holds a reference to the User document.
        //This is useful when a field can hold references to different documents.
        $matches = [];
        if (
            preg_match('/(?<=\:)(App\\\\[A-Za-z\\\\]+)(?=\.)/', $filterField, $matches) === 1
            && class_exists($matches[0])
            && in_array(DocumentInterface::class, class_implements($matches[0]))
        ) {
            return true;
        }

        return false;
    }


    /**
     * Returns the fully qualified name of the document that is referenced by the given field.
     * For example if the field is 'user.firstName' and the current document 
     * has a field 'user' that holds a reference to the User document, the method will return 'App\Document\User'
     */
    private function getReferencedDocumentName(string $documentName, string $filterField): ?string
    {
        $documentReferencesMap = $this->getDocumentReferencesMap($documentName);
        foreach ($documentReferencesMap as $documentReferencedField => $referencedDocumentName) {
            if (
                $filterField !== $documentReferencedField
                && str_contains($filterField, '.')
                && str_starts_with($filterField, sprintf('%s.', $documentReferencedField))
            ) {
                return $referencedDocumentName;
            }
        }

        //Join filters can be applied by defining the fully qualified name of the referenced document
        //in the filter field. For example 'user:App\Document\User.firstName' is a join filter if the current document
        //contains a field named user that holds a reference to the User document.
        //We must therefore extract the fully qualified name of the referenced document from the filter field.
        $matches = [];
        if (
            preg_match('/(?<=\:)(App\\\\[A-Za-z\\\\]+)(?=\.)/', $filterField, $matches) === 1
            && class_exists($matches[0])
            && in_array(DocumentInterface::class, class_implements($matches[0]))
        ) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Returns the field name to use in the filter that is applied to a join query.
     * For example if the filter field is 'user.firstName' and the current document 
     * has a field 'user' that holds a reference to the User document, the method will return 'firstName'
     * because to resolve the join we must query the User document and filter on the 'firstName' field.
     */
    private function getJoinQueryFilterFieldName(string $documentName, string $filterField): string
    {
        //Another way to apply join filters is to use the fully qualified name of the referenced document
        //in the filter field. For example 'user:App\Document\User.firstName' is a join filter if the current document
        //contains a field named user that holds a reference to the User document.
        //In this case the join filter field is the part of the string that comes after the first dot.
        if (preg_match('/(?<=\:)(App\\\\[A-Za-z\\\\]+)(?=\.)/', $filterField) === 1) {
            return explode('.', explode(':', $filterField, 2)[1], 2)[1];
        }

        $documentReferencesMap = $this->getDocumentReferencesMap($documentName);
        foreach (array_keys($documentReferencesMap) as $documentReferencedField) {
            if (strpos($filterField, $documentReferencedField) === 0) {
                //+1 to remove the dot!
                return substr($filterField, strlen($documentReferencedField) + 1);
            }
        }

        return '';
    }

    /**
     * Returns the field name for the filter to apply to the main query of a solved join filter.
     * For example if the filter field is 'user.firstName' and the current document 
     * has a field 'user' that holds a reference to the User document, the method will return 'user'
     * because after we have resolved the join values and have the ids of the referenced documents to filter by
     * we must filter by user ids in the main query.
     */
    private function getJoinFilterFieldName(string $documentName, string $filterField): string
    {
        $documentReferencesMap = $this->getDocumentReferencesMap($documentName);
        foreach (array_keys($documentReferencesMap) as $documentReferencedField) {
            if (strpos($filterField, $documentReferencedField) === 0) {
                return $documentReferencedField;
            }
        }

        //Another way to apply join filters is to use the fully qualified name of the referenced document
        //in the filter field. For example 'user:App\Document\User.firstName' is a join filter if the current document
        //contains a field named user that holds a reference to the User document.
        //In this case the join filter field is the part of the string that comes before the colon (:)
        if (preg_match('/(?<=\:)(App\\\\[A-Za-z\\\\]+)(?=\.)/', $filterField) === 1) {
            return explode(':', $filterField, 2)[0];
        }

        return '';
    }

    private function getDocumentReferencesMap(string $documentName): array
    {
        if (!in_array(RelationalDocumentInterface::class, class_implements($documentName))) {
            return array();
        }

        $referencesMap = $documentName::getReferencesMap();
        $keys = array_map('strlen', array_keys($referencesMap));
        array_multisort($keys, SORT_DESC, $referencesMap);
        return $referencesMap;
    }

    private function isReferencedField(string $documentName, string $filterField): bool
    {
        return in_array(RelationalDocumentInterface::class, class_implements($documentName))
            && array_key_exists($filterField, $documentName::getReferencesMap());
    }

    // ==== GENERIC QUERY HELPER METHODS =============================

    private function getDocumentsTotalCount(string $documentName, QueryCriteria $queryCriteria): int
    {
        $queryBuilder = $this->documentManager->createQueryBuilder($documentName);
        $this->filter($documentName, $queryBuilder, $queryCriteria);
        $this->getCount($queryBuilder);
        return $queryBuilder->getQuery(self::QUERY_OPTIONS)->execute();
    }

    private function getCount(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->count();
    }

    private function getDistinct(QueryBuilder $queryBuilder, QueryCriteria $queryCriteria): void
    {
        if ($queryCriteria->getDistinctField() === null) {
            throw new InvalidArgumentException(
                'Cannot build distinct query because distinct field is not set',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        $queryBuilder
            ->distinct($queryCriteria->getDistinctField())
            ->select($queryCriteria->getDistinctField());
    }

    /**
     * Instruct the query builder to prime the specified fields.
     * This is useful when you know that you will need the referenced documents of a field.
     */
    private function primeFields(QueryBuilder $queryBuilder, QueryCriteria $queryCriteria): void
    {
        if ($queryCriteria->isReadOnly()) {
            return;
        }

        foreach ($queryCriteria->getFieldsToPrime() as $fieldToPrime) {
            $queryBuilder->field($fieldToPrime)->prime(true);
        }
    }

    /**
     * Instruct the query builder to select the specified fields of the document
     */
    private function selectFields(QueryBuilder $queryBuilder, QueryCriteria $queryCriteria): void
    {
        //for distinct query the fields to select is handled by the dedicated method
        if ($queryCriteria->getDistinctField() !== null) {
            return;
        }

        if (count($queryCriteria->getFieldsToSelect()) > 0) {
            $queryBuilder->select($queryCriteria->getFieldsToSelect());
        } elseif (count($queryCriteria->getFieldsToExclude()) > 0) {
            $queryBuilder->exclude($queryCriteria->getFieldsToExclude());
        }
    }

    /**
     * Set the query as readOnly or not
     */
    private function readOnly(QueryBuilder $queryBuilder, QueryCriteria $queryCriteria): void
    {
        $queryBuilder->readOnly($queryCriteria->isReadOnly());
    }

    // ==== CACHE HELPER METHODS =============================

    private function hydrateDocument(string $documentName, ?array $notHydratedDocumentData): ?DocumentInterface
    {
        if (isset($notHydratedDocumentData)) {
            $document = new $documentName();
            $this->documentManager->getHydratorFactory()->hydrate($document, $notHydratedDocumentData);
            return $document;
        }
        return null;
    }

    private function hydrateDocuments(string $documentName, array $notHydratedDocumentsData): Collection
    {
        $documents = new ArrayCollection();
        foreach ($notHydratedDocumentsData as $notHydratedDocument) {
            $document = new $documentName();
            $this->documentManager->getHydratorFactory()->hydrate($document, $notHydratedDocument);
            $documents->add($document);
        }
        return $documents;
    }
}
