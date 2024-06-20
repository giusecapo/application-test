<?php

declare(strict_types=1);

namespace App\Service\QueryHelper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class SubsetQueryResult
{
    /**
     * @var Collection of objects implementing DocumentInterface
     */
    private Collection $documents;

    private ?int $documentsTotalCount;

    private bool $hasPreviousPage;

    private bool $hasNextPage;

    /**
     * @var QueryCriteria the query criteria object
     *  which lead to this instance of SubsetQueryResult
     */
    private ?QueryCriteria $queryCriteria;


    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->documentsTotalCount = null;
        $this->hasPreviousPage = false;
        $this->hasNextPage = false;
        $this->queryCriteria = null;
    }


    /**
     * Get a collection of objects implementing DocumentInterface(s)
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * @param  Collection  $documents  a collection of objects implementing DocumentInterface(s)
     */
    public function setDocuments(Collection $documents): SubsetQueryResult
    {
        $this->documents = $documents;

        return $this;
    }

    public function getDocumentsTotalCount(): ?int
    {
        return $this->documentsTotalCount;
    }


    public function setDocumentsTotalCount(?int $documentsTotalCount): SubsetQueryResult
    {
        $this->documentsTotalCount = $documentsTotalCount;

        return $this;
    }

    public function getHasPreviousPage(): bool
    {
        return $this->hasPreviousPage;
    }

    public function setHasPreviousPage(bool $hasPreviousPage): SubsetQueryResult
    {
        $this->hasPreviousPage = $hasPreviousPage;

        return $this;
    }

    public function getHasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    public function setHasNextPage(bool $hasNextPage): SubsetQueryResult
    {
        $this->hasNextPage = $hasNextPage;

        return $this;
    }

    public function getQueryCriteria(): ?QueryCriteria
    {
        return $this->queryCriteria;
    }

    public function setQueryCriteria(QueryCriteria $queryCriteria): SubsetQueryResult
    {
        $this->queryCriteria = $queryCriteria;

        return $this;
    }
}
