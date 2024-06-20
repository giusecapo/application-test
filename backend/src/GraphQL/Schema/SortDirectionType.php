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
final class SortDirectionType extends EnumType implements GeneratedTypeInterface
{
    const NAME = 'SortDirection';

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'SortDirection',
            'values' => [
                'ASC' => [
                    'name' => 'ASC',
                    'value' => 1,
                    'deprecationReason' => null,
                    'description' => null,
                ],
                'DESC' => [
                    'name' => 'DESC',
                    'value' => -1,
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
