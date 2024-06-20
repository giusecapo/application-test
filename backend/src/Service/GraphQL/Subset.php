<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Service\QueryHelper\QueryCriteria;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class Subset
{

    private Collection $items;

    private int $count;

    private ?int $totalCount;

    private bool $hasNextPage;

    private bool $hasPreviousPage;

    private ?QueryCriteria $queryCriteria;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->totalCount = null;
        $this->count = 0;
        $this->hasNextPage = false;
        $this->hasPreviousPage = false;
        $this->queryCriteria = new QueryCriteria();
    }


    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems(Collection $items): Subset
    {
        $this->items = $items;
        $this->count = $items->count();

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }


    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    public function setTotalCount(?int $totalCount): Subset
    {
        $this->totalCount = $totalCount;

        return $this;
    }

    public function getHasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    public function setHasNextPage(bool $hasNextPage): Subset
    {
        $this->hasNextPage = $hasNextPage;

        return $this;
    }

    public function getHasPreviousPage(): bool
    {
        return $this->hasPreviousPage;
    }

    public function setHasPreviousPage(bool $hasPreviousPage): Subset
    {
        $this->hasPreviousPage = $hasPreviousPage;

        return $this;
    }

    public function getQueryCriteria(): QueryCriteria
    {
        return $this->queryCriteria;
    }

    public function setQueryCriteria(QueryCriteria $queryCriteria): self
    {
        $this->queryCriteria = $queryCriteria;

        return $this;
    }
}
