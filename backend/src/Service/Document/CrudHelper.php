<?php

declare(strict_types=1);

namespace App\Service\Document;

use App\Contract\Document\AugmentedDocumentInterface;
use App\Contract\Document\ConcurrencySafeDocumentInterface;
use App\Contract\Document\DocumentInterface;
use App\Contract\DocumentModel\Mapper\MapperInterface;
use App\Service\Constant\Users;
use App\Service\QueryHelper\QueryCriteria;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Security;
use App\Service\QueryHelper\SubsetQueryResult;
use App\Service\QueryHelper\QueryHelper;
use Doctrine\ODM\MongoDB\LockMode;


/**
 * The class CrudHelper provides helper methods 
 * to read documents from storage and persist them.
 */
final class CrudHelper
{
    /**
     * When set to true, the flush operation 
     * will be executed in a transaction
     */
    private bool $forceUseTransaction;

    public function __construct(
        private Security $security,
        private QueryHelper $queryHelper,
        private EventProvider $eventProvider

    ) {
        $this->forceUseTransaction = false;
    }

    /**
     * Apply changes to a document with a direct update query
     */
    public function updateOneNow(string $documentName, QueryCriteria $queryCriteria): void
    {
        $this->queryHelper->updateOneNow($documentName, $queryCriteria);
    }

    /**
     * Apply changes to many documents with a direct update query
     */
    public function updateManyNow(string $documentName, QueryCriteria $queryCriteria): void
    {
        $this->queryHelper->updateManyNow($documentName, $queryCriteria);
    }

    /**
     * Delete documents with a direct delete query
     */
    public function deleteNow(string $documentName, QueryCriteria $queryCriteria): void
    {
        $this->queryHelper->deleteNow($documentName, $queryCriteria);
    }


    /**
     * Returns a SubsetQueryResult for a cursor based or offset/limit pagination query
     */
    public function getSubset(string $documentName, QueryCriteria $queryCriteria): SubsetQueryResult
    {
        $subset = $this->queryHelper->getSubset($documentName, $queryCriteria);

        return $subset;
    }


    /**
     * Find documents in a collection. Optionally filter and slice the result
     */
    public function find(string $documentName, QueryCriteria $queryCriteria): Collection
    {
        $documents = $this->queryHelper->find($documentName, $queryCriteria);

        return $documents;
    }


    /**
     * Find a single document in a collection.
     */
    public function getSingleResult(string $documentName, QueryCriteria $queryCriteria): ?DocumentInterface
    {
        $document = $this->queryHelper->getSingleResult($documentName, $queryCriteria);

        return $document;
    }


    /**
     * Count the number of documents matching the given filters
     */
    public function count(string $documentName, QueryCriteria $queryCriteria): int
    {
        return $this->queryHelper->count($documentName, $queryCriteria);
    }

    /**
     * Sum the values of the given field of all documents matching the given query
     */
    public function sum(string $documentName, QueryCriteria $queryCriteria): int|float
    {
        return $this->queryHelper->sum($documentName, $queryCriteria);
    }

    /**
     * Returns all distinct values for a given field 
     */
    public function distinct(string $documentName, QueryCriteria $queryCriteria): Collection
    {
        return $this->queryHelper->distinct($documentName, $queryCriteria);
    }

    /**
     * Schedule a document for insertion in persistence storage
     */
    public function createDocument(DocumentInterface $document): void
    {
        $this->queryHelper->persist($document);
    }


    /**
     * Schedule a document for update in persistence storage
     */
    public function updateDocument(DocumentInterface $document, ?int $expectedVersion): void
    {
        if ($document instanceof ConcurrencySafeDocumentInterface &&  isset($expectedVersion)) {
            $this->queryHelper->lock($document, LockMode::OPTIMISTIC, $expectedVersion);
        }

        $this->queryHelper->persist($document);
    }

    /**
     * Delete a document from persistence storage.
     * The document is physically deleted and cannot be restored later.
     */
    public function hardDelete(DocumentInterface $document, ?int $expectedVersion = null): void
    {
        if ($document instanceof ConcurrencySafeDocumentInterface && isset($expectedVersion)) {
            $this->queryHelper->lock($document, LockMode::OPTIMISTIC, $expectedVersion);
        }
        $this->queryHelper->remove($document);
    }

    /**
     * Apply all scheduled changes to persistence storage
     */
    public function flush(array $options = [], bool $useTransaction = false): void
    {
        $this->eventProvider->dispatchBufferedEvents(EventProvider::ON_PRE_FLUSH_BUFFER);
        $this->queryHelper->flush(
            $options,
            // the prop forceUseTransaction has precedence over the method's argument 
            $this->forceUseTransaction ? $this->forceUseTransaction : $useTransaction
        );
        //reset the master 'useTransaction' prop state
        $this->forceUseTransaction = false;
        $this->eventProvider->dispatchBufferedEvents(EventProvider::ON_POST_FLUSH_BUFFER);
    }

    /**
     * Clear the unit of work and all identity maps related to the documents
     */
    public function clear(): void
    {
        $this->queryHelper->clear();
    }


    /**
     * Sets the value of forceUseTransaction to true
     * which forces the next call to flush() to be executed 
     * within a multi-document transaction
     */
    public function forceUseTransaction(): void
    {
        $this->forceUseTransaction = true;
    }

    /**
     * Sets the value of forceUseTransaction to false
     */
    public function resetUseTransaction(): void
    {
        $this->forceUseTransaction = false;
    }
}
