<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Service\QueryHelper\FiltersDescriptor;
use App\Service\QueryHelper\QueryCriteria;

final class GetByFieldValuesQueryArgumentsProvider
{

    /**
     * Builds and returns the QueryCriteria object to use in find queries when resolving references with buffers
     */
    public function toQueryCriteria(string $fieldName, array $values): QueryCriteria
    {
        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->in($fieldName, $values);

        $queryCriteria = new QueryCriteria();
        $queryCriteria
            ->setFiltersDescriptor($filtersDescriptor)
            ->readOnly();

        return $queryCriteria;
    }
}
