<?php

declare(strict_types=1);

namespace App\Service\Document;

use App\Contract\Document\AugmentedDocumentInterface;
use App\Contract\Document\DocumentInterface;
use App\Contract\Document\RelationalDocumentInterface;
use App\Contract\Document\EmbeddedDocumentInterface;
use App\Contract\Document\FileDataInterface;
use App\Contract\DocumentModel\Mapper\MapperInterface;
use App\Service\Document\CrudHelper;
use App\Service\Document\EventProvider;
use App\Service\Document\SecurityProvider;
use App\Service\Document\ValidationHelper;
use App\Service\QueryHelper\FiltersDescriptor;
use App\Service\QueryHelper\QueryCriteria;
use App\Service\QueryHelper\SubsetQueryResult;
use App\Utility\MethodsBuilder;
use Doctrine\Common\Collections\Collection;

final class ReadWriteHelper
{

    public function __construct(
        private CrudHelper $crudHelper,
        private ValidationHelper $validationHelper,
        private EventProvider $eventProvider,
        private SecurityProvider $securityProvider,
        private string $createAttribute,
        private string $readAttribute,
        private string $updateAttribute,
        private string $deleteAttribute,
        private string $onPreCreateEventClassName,
        private string $onAfterCreateEventClassName,
        private string $beforeCreatedEventClassName,
        private string $afterCreatedEventClassName,
        private string $onPreUpdateEventClassName,
        private string $onAfterUpdateEventClassName,
        private string $beforeUpdatedEventClassName,
        private string $afterUpdatedEventClassName,
        private string $onPreDeleteEventClassName,
        private string $onAfterDeleteEventClassName,
        private string $beforeDeletedEventClassName,
        private string $afterDeletedEventClassName
    ) {
    }

    /**
     * @internal
     */
    public function updateOneNow(string $documentName, QueryCriteria $queryCriteria): void
    {
        $this->crudHelper->updateOneNow($documentName, $queryCriteria);
    }

    /**
     * @internal
     */
    public function updateManyNow(string $documentName, QueryCriteria $queryCriteria): void
    {
        $this->crudHelper->updateManyNow($documentName, $queryCriteria);
    }

    /**
     * @internal
     */
    public function deleteNow(string $documentName, QueryCriteria $queryCriteria): void
    {
        $this->crudHelper->deleteNow($documentName, $queryCriteria);
    }

    /**
     * Returns a SubsetQueryResult for a cursor based or offset/limit pagination query
     */
    public function getSubset(string $documentName, QueryCriteria $queryCriteria, bool $checkAuthorization = true): SubsetQueryResult
    {
        $subsetQueryResult =  $this->crudHelper->getSubset($documentName, $queryCriteria);

        if ($checkAuthorization) {
            $this->securityProvider->denyDocumentsAccessUnlessGranted($this->readAttribute, $subsetQueryResult->getDocuments());
        }

        return $subsetQueryResult;
    }

    /**
     * Find documents in a collection. Optionally filter and slice the result
     */
    public function find(string $documentName, QueryCriteria $queryCriteria, bool $checkAuthorization = true): Collection
    {
        $documents = $this->crudHelper->find($documentName, $queryCriteria);

        if ($checkAuthorization) {
            $this->securityProvider->denyDocumentsAccessUnlessGranted($this->readAttribute, $documents);
        }

        return $documents;
    }

    public function getByFieldValues(
        string $documentName,
        string $fieldName,
        array $values,
        bool $checkAuthorization = true
    ): Collection {
        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->in($fieldName, $values);

        $queryCriteria = new QueryCriteria();
        $queryCriteria->setFiltersDescriptor($filtersDescriptor);

        return $this->find($documentName, $queryCriteria, $checkAuthorization);
    }

    public function getSingleResult(string $documentName, QueryCriteria $queryCriteria, bool $checkAuthorization = true): ?DocumentInterface
    {
        $document = $this->crudHelper->getSingleResult($documentName, $queryCriteria);
        if (!isset($document)) {
            return null;
        }

        if ($checkAuthorization) {
            $this->securityProvider->denyDocumentAccessUnlessGranted($this->readAttribute, $document);
        }

        return $document;
    }

    public function getByFieldValue(
        string $documentName,
        string $fieldName,
        mixed $value,
        bool $checkAuthorization = true
    ): ?DocumentInterface {
        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->equals($fieldName, $value);

        $queryCriteria = new QueryCriteria();
        $queryCriteria->setFiltersDescriptor($filtersDescriptor);

        return $this->getSingleResult($documentName, $queryCriteria, $checkAuthorization);
    }

    public function getManyByFieldValue(
        string $documentName,
        string $fieldName,
        mixed $value,
        bool $checkAuthorization = true
    ): Collection {
        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->equals($fieldName, $value);

        $queryCriteria = new QueryCriteria();
        $queryCriteria->setFiltersDescriptor($filtersDescriptor);

        return $this->find($documentName, $queryCriteria, $checkAuthorization);
    }

    /**
     * Get the DocumentInterface document referenced by a given document
     */
    public function getReferencedDocument(
        string $documentName,
        DocumentInterface|EmbeddedDocumentInterface $document,
        string $fieldName,
        bool $checkAuthorization = true
    ): ?DocumentInterface {
        $idGetterMethod = MethodsBuilder::toGetIdMethod($fieldName);
        $id = $document->$idGetterMethod();
        if (!isset($id)) {
            return null;
        }

        return $this->getByFieldValue($documentName, 'id', $document->$idGetterMethod(), $checkAuthorization);
    }


    /**
     * Get the DocumentInterface documents referenced by a given document 
     */
    public function getReferencedDocuments(
        string $documentName,
        DocumentInterface|EmbeddedDocumentInterface $document,
        string $fieldName,
        bool $checkAuthorization = true
    ): Collection {
        $idsGetterMethod = MethodsBuilder::toGetIdsMethod($fieldName);
        return $this->getByFieldValues($documentName, 'id', $document->$idsGetterMethod(), $checkAuthorization);
    }


    /**
     * Get all DocumentInterface documents referencing the given document
     */
    public function getReferencingDocuments(
        string $documentName,
        DocumentInterface $document,
        string $fieldName,
        bool $checkAuthorization = true
    ): Collection {
        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->references($fieldName, $document);

        $queryCriteria = new QueryCriteria();
        $queryCriteria->setFiltersDescriptor($filtersDescriptor);

        return $this->find($documentName, $queryCriteria, $checkAuthorization);
    }


    /**
     * Count the number of documents matching the given filters
     */
    public function count(string $documentName, QueryCriteria $queryCriteria, bool $checkAuthorization = true): int
    {
        return $this->crudHelper->count($documentName, $queryCriteria);
    }

    /**
     * Fnd all distinct values of the given field
     */
    public function distinct(string $documentName, QueryCriteria $queryCriteria, bool $checkAuthorization = true): Collection
    {
        return $this->crudHelper->distinct($documentName, $queryCriteria);
    }


    /**
     * Sum the values of the given field of all documents matching the given query
     */
    public function sum(string $documentName, QueryCriteria $queryCriteria): float|int
    {
        return $this->crudHelper->sum($documentName, $queryCriteria);
    }

    public function create(
        DocumentInterface $document,
        ?array $validationGroups = null,
        bool $checkAuthorization = true
    ): void {
        if ($checkAuthorization) {
            $this->securityProvider->denyDocumentAccessUnlessGranted($this->createAttribute, $document);
        }

        $this->validationHelper->validateOnCreate($document, $validationGroups);

        $this->eventProvider->createEventAndDispatch($this->onPreCreateEventClassName, $document);
        $this->crudHelper->createDocument($document);
        $this->eventProvider->createEventAndDispatch($this->onAfterCreateEventClassName, $document);

        $this->eventProvider->createEventAndAddToBuffers(
            [
                EventProvider::ON_PRE_FLUSH_BUFFER => $this->beforeCreatedEventClassName,
                EventProvider::ON_POST_FLUSH_BUFFER => $this->afterCreatedEventClassName
            ],
            $document
        );
    }


    public function update(
        DocumentInterface $document,
        ?array $validationGroups = null,
        ?int $expectedVersion = null,
        bool $checkAuthorization = true,
        ?MapperInterface $documentMapper = null
    ): void {
        if ($checkAuthorization) {
            $this->securityProvider->denyDocumentAccessUnlessGranted($this->updateAttribute, $document);
        }
        $this->validationHelper->validateOnUpdate($document, $validationGroups);

        $this->eventProvider->createEventAndDispatch($this->onPreUpdateEventClassName, $document);
        $this->crudHelper->updateDocument($document, $expectedVersion, $documentMapper);
        $this->eventProvider->createEventAndDispatch($this->onAfterUpdateEventClassName, $document);

        $this->eventProvider->createEventAndAddToBuffers(
            [
                EventProvider::ON_PRE_FLUSH_BUFFER => $this->beforeUpdatedEventClassName,
                EventProvider::ON_POST_FLUSH_BUFFER => $this->afterUpdatedEventClassName
            ],
            $document
        );
    }


    public function delete(
        DocumentInterface $document,
        ?int $expectedVersion = null,
        bool $checkAuthorization = true,
    ): void {
        if ($checkAuthorization) {
            $this->securityProvider->denyDocumentAccessUnlessGranted($this->deleteAttribute, $document);
        }

        $this->eventProvider->createEventAndDispatch($this->onPreDeleteEventClassName, $document);
        $this->crudHelper->hardDelete($document, $expectedVersion);
        $this->eventProvider->createEventAndDispatch($this->onAfterDeleteEventClassName, $document);

        $this->eventProvider->createEventAndAddToBuffers(
            [
                EventProvider::ON_PRE_FLUSH_BUFFER => $this->beforeDeletedEventClassName,
                EventProvider::ON_POST_FLUSH_BUFFER => $this->afterDeletedEventClassName
            ],
            $document
        );
    }

    /**
     * Flush the unit of work.
     * This operations is not scoped at the class level:
     * flushing will commit all changes registered by the unit of work
     */
    public function flush(bool $useTransaction = false): void
    {
        $this->crudHelper->flush([], $useTransaction);
    }

    /**
     * Clear the entire unit of work and other internal caches/identity maps
     */
    public function clear(): void
    {
        $this->crudHelper->clear();
    }

    /**
     * Force the next flush operation to be executed
     * within a multi-document transaction
     */
    public function forceUseTransaction(): void
    {
        $this->crudHelper->forceUseTransaction();
    }

    /**
     * Sets the value of forceUseTransaction to false
     */
    public function resetUseTransaction(): void
    {
        $this->crudHelper->resetUseTransaction();
    }
}
