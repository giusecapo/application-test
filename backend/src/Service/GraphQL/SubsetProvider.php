<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Service\QueryHelper\SubsetQueryResult;

final class SubsetProvider
{

    public function toSubset(SubsetQueryResult $subsetQueryResult): Subset
    {
        $subset = new Subset();
        $subset
            ->setItems($subsetQueryResult->getDocuments())
            ->setTotalCount($subsetQueryResult->getDocumentsTotalCount())
            ->setHasNextPage($subsetQueryResult->getHasNextPage())
            ->setHasPreviousPage($subsetQueryResult->getHasPreviousPage())
            ->setQueryCriteria($subsetQueryResult->getQueryCriteria());

        return $subset;
    }
}
