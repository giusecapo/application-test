<?php
namespace App\GraphQL\Schema;

use GraphQL\Type\Definition\EnumType;
use Overblog\GraphQLBundle\Definition\ConfigProcessor;
use Overblog\GraphQLBundle\Definition\GlobalVariables;
use Overblog\GraphQLBundle\Definition\LazyConfig;
use Overblog\GraphQLBundle\Definition\Type\GeneratedTypeInterface;

/**
 * THIS FILE WAS GENERATED AND SHOULD NOT BE MODIFIED!
 */
final class FilterOperatorType extends EnumType implements GeneratedTypeInterface
{
    const NAME = 'FilterOperator';

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'FilterOperator',
            'values' => [
                'IN' => [
                    'name' => 'IN',
                    'value' => 'in',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'NOT_IN' => [
                    'name' => 'NOT_IN',
                    'value' => 'notIn',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'EQUALS' => [
                    'name' => 'EQUALS',
                    'value' => 'equals',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'EQUALS_CASE_INSENSITIVE' => [
                    'name' => 'EQUALS_CASE_INSENSITIVE',
                    'value' => 'equalsCaseInsensitive',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'EQUALS_REGEX' => [
                    'name' => 'EQUALS_REGEX',
                    'value' => 'equalsRegex',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'CONTAINS' => [
                    'name' => 'CONTAINS',
                    'value' => 'contains',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'STARTS_WITH' => [
                    'name' => 'STARTS_WITH',
                    'value' => 'startsWith',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'NOT_EQUAL' => [
                    'name' => 'NOT_EQUAL',
                    'value' => 'notEqual',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'TEXT' => [
                    'name' => 'TEXT',
                    'value' => 'text',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'GT' => [
                    'name' => 'GT',
                    'value' => 'gt',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'GTE' => [
                    'name' => 'GTE',
                    'value' => 'gte',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'LT' => [
                    'name' => 'LT',
                    'value' => 'lt',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'LTE' => [
                    'name' => 'LTE',
                    'value' => 'lte',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'SIZE' => [
                    'name' => 'SIZE',
                    'value' => 'size',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'EXISTS' => [
                    'name' => 'EXISTS',
                    'value' => 'exists',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'ALL' => [
                    'name' => 'ALL',
                    'value' => 'all',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'GEO_WITHIN_POLYGON' => [
                    'name' => 'GEO_WITHIN_POLYGON',
                    'value' => 'geoWithinPolygon',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'GEO_WITHIN_CIRCLE' => [
                    'name' => 'GEO_WITHIN_CIRCLE',
                    'value' => 'geoWithinCircle',
                    'deprecationReason' => null,
                    'description' => null,
                ],
            ],
            'description' => null,
        ];
        };
        $config = $configProcessor->process(LazyConfig::create($configLoader, $globalVariables))->load();
        parent::__construct($config);
    }
}
