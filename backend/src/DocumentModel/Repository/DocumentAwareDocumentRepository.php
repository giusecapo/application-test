<?php

declare(strict_types=1);

namespace App\DocumentModel\Repository;

use App\Contract\Document\DocumentInterface;
use App\Contract\DocumentModel\Repository\RepositoryInterface;
use App\Service\Document\ReadWriteHelper;
use Doctrine\Common\Collections\Collection;
use App\Service\QueryHelper\QueryCriteria;

final class DocumentAwareDocumentRepository extends AbstractRepository implements RepositoryInterface
{

    public const DOCUMENT_NAME = DocumentInterface::class;

    public function __construct(
        protected ReadWriteHelper $readWriteHelper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDocumentName(): string
    {
        return self::DOCUMENT_NAME;
    }

    public function getByIds(
        string $documentName,
        array $ids,
        bool $checkAuthorization = true
    ): Collection {
        return $this->readWriteHelper->getByFieldValues($documentName, 'id', $ids, $checkAuthorization);
    }

    public function getById(
        string $documentName,
        string $id,
        bool $checkAuthorization = true,
        bool $trackRead = false
    ): ?DocumentInterface {
        return $this->readWriteHelper->getByFieldValue($documentName, 'id', $id, $checkAuthorization);
    }

    public function find(
        string $documentName,
        QueryCriteria $queryCriteria,
        bool $checkAuthorization = true,
    ): Collection {
        return $this->readWriteHelper->find($documentName, $queryCriteria, $checkAuthorization);
    }
}
