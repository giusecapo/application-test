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
final class FilterValueTypeType extends EnumType implements GeneratedTypeInterface
{
    const NAME = 'FilterValueType';

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'FilterValueType',
            'values' => [
                'STRING' => [
                    'name' => 'STRING',
                    'value' => 'STRING',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'BOOL' => [
                    'name' => 'BOOL',
                    'value' => 'BOOL',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'INT' => [
                    'name' => 'INT',
                    'value' => 'INT',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'FLOAT' => [
                    'name' => 'FLOAT',
                    'value' => 'FLOAT',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'DATETIME' => [
                    'name' => 'DATETIME',
                    'value' => 'DATETIME',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'STRING_LIST' => [
                    'name' => 'STRING_LIST',
                    'value' => 'STRING_LIST',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'INT_LIST' => [
                    'name' => 'INT_LIST',
                    'value' => 'INT_LIST',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'FLOAT_LIST' => [
                    'name' => 'FLOAT_LIST',
                    'value' => 'FLOAT_LIST',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'BOOL_LIST' => [
                    'name' => 'BOOL_LIST',
                    'value' => 'BOOL_LIST',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'DATETIME_LIST' => [
                    'name' => 'DATETIME_LIST',
                    'value' => 'DATETIME_LIST',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'REFERENCE' => [
                    'name' => 'REFERENCE',
                    'value' => 'REFERENCE',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'REFERENCE_LIST' => [
                    'name' => 'REFERENCE_LIST',
                    'value' => 'REFERENCE_LIST',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'STRING_LIST_NULLABLE' => [
                    'name' => 'STRING_LIST_NULLABLE',
                    'value' => 'STRING_LIST_NULLABLE',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'INT_LIST_NULLABLE' => [
                    'name' => 'INT_LIST_NULLABLE',
                    'value' => 'INT_LIST_NULLABLE',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'FLOAT_LIST_NULLABLE' => [
                    'name' => 'FLOAT_LIST_NULLABLE',
                    'value' => 'FLOAT_LIST_NULLABLE',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'BOOL_LIST_NULLABLE' => [
                    'name' => 'BOOL_LIST_NULLABLE',
                    'value' => 'BOOL_LIST_NULLABLE',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'DATETIME_LIST_NULLABLE' => [
                    'name' => 'DATETIME_LIST_NULLABLE',
                    'value' => 'DATETIME_LIST_NULLABLE',
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'POLYGON' => [
                    'name' => 'POLYGON',
                    'value' => 'POLYGON',
                    'deprecationReason' => null,
                    'description' => 'Value format: lng,lat|lng,lat|lng,lat. The edges of the polygon should not cross. The last closing point can be omitted.',
                ],
                'CIRCLE' => [
                    'name' => 'CIRCLE',
                    'value' => 'CIRCLE',
                    'deprecationReason' => null,
                    'description' => 'Value format: lng,lat|distance. The server utilizes the given values to compute the points of a polygon,  which approximates a circle centered at the provided latitude and longitude, and a radius equal to the provided distance on the earth surface.  The distance is expressed in meters and must be a value greater than 0 and lower than 10.000.000.',
                ],
            ],
            'description' => 'Provide a hint on how to cast the filter\'s value.',
        ];
        };
        $config = $configProcessor->process(LazyConfig::create($configLoader, $globalVariables))->load();
        parent::__construct($config);
    }
}
