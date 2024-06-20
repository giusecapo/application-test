<?php

declare(strict_types=1);

namespace App\DocumentModel\Repository;

use App\Contract\DocumentModel\Repository\RepositoryInterface;
use App\Service\Document\ReadWriteHelper;

abstract class AbstractRepository implements RepositoryInterface
{

    protected ReadWriteHelper $readWriteHelper;

    /**
     * @inheritDoc
     */
    abstract public function getDocumentName(): string;

    /**
     * Disable a query filter (ODM query filters)
     */
    public function disableFilter(string $filterName): void
    {
        $this->readWriteHelper->disableFilter($filterName);
    }

    /**
     * Enable a query filter (ODM query filter)
     */
    public function enableFilter(string $filterName): void
    {
        $this->readWriteHelper->enableFilter($filterName);
    }
}
