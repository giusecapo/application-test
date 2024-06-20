<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Service\GraphQL\PageInfos;
use App\Service\QueryHelper\QueryCriteria;
use Doctrine\Common\Collections\ArrayCollection;


final class RelayConnection
{

    private PageInfos $pageInfos;

    private ArrayCollection $edges;

    private int $count;

    private ?int $totalCount;

    private QueryCriteria $queryCriteria;

    public function __construct()
    {
        $this->edges = new ArrayCollection();
        $this->totalCount = null;
        $this->count = 0;
        $this->pageInfos = new PageInfos();
        $this->queryCriteria = new QueryCriteria();
    }

    public function getPageInfos(): ?PageInfos
    {
        return $this->pageInfos;
    }

    public function setPageInfos(PageInfos $pageInfos): RelayConnection
    {
        $this->pageInfos = $pageInfos;

        return $this;
    }

    public function getEdges(): ArrayCollection
    {
        return $this->edges;
    }


    public function setEdges(ArrayCollection $edges): RelayConnection
    {
        $this->edges = $edges;
        $this->count = $edges->count();

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

    public function setTotalCount(?int $totalCount): RelayConnection
    {
        $this->totalCount = $totalCount;

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
