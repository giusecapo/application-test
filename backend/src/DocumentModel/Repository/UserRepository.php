<?php

declare(strict_types=1);

namespace App\DocumentModel\Repository;

use App\Contract\DocumentModel\Repository\RepositoryInterface;
use App\Document\User;
use App\Service\Document\ReadWriteHelper;
use App\Service\QueryHelper\QueryCriteria;
use App\Service\QueryHelper\SubsetQueryResult;
use Doctrine\Common\Collections\Collection;

final class UserRepository implements RepositoryInterface
{

    public const DOCUMENT_NAME = User::class;

    public function __construct(protected ReadWriteHelper $readWriteHelper)
    {
    }

    /**
     * @inheritDoc
     */
    public function getDocumentName(): string
    {
        return self::DOCUMENT_NAME;
    }

    public function getByIds(array $ids, bool $checkAuthorization = true): Collection
    {
        return $this->readWriteHelper->getByFieldValues(self::DOCUMENT_NAME, 'id', $ids, $checkAuthorization);
    }

    public function getById(string $id, bool $checkAuthorization = true): ?User
    {
        return $this->readWriteHelper->getByFieldValue(self::DOCUMENT_NAME, 'id', $id, $checkAuthorization);
    }

    public function getByUsernames(array $usernames,  bool $checkAuthorization = true): Collection
    {
        return $this->readWriteHelper->getByFieldValues(self::DOCUMENT_NAME, 'username', $usernames, $checkAuthorization);
    }

    public function getByUsername(string $username, bool $checkAuthorization = true): ?User
    {
        return $this->readWriteHelper->getByFieldValue(self::DOCUMENT_NAME, 'username', $username, $checkAuthorization);
    }

    public function getSubset(
        QueryCriteria $queryCriteria,
        bool $checkAuthorization = true
    ): SubsetQueryResult {
        return $this->readWriteHelper->getSubset(self::DOCUMENT_NAME, $queryCriteria, $checkAuthorization);
    }

    public function find(
        QueryCriteria $queryCriteria,
        bool $checkAuthorization = true
    ): Collection {
        return $this->readWriteHelper->find(self::DOCUMENT_NAME, $queryCriteria, $checkAuthorization);
    }

    public function getSingleResult(
        QueryCriteria $queryCriteria,
        bool $checkAuthorization = true
    ): ?User {
        return $this->readWriteHelper->getSingleResult(self::DOCUMENT_NAME, $queryCriteria, $checkAuthorization);
    }
}
