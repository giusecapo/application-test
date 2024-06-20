<?php
namespace App\GraphQL\Schema;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Overblog\GraphQLBundle\Definition\ConfigProcessor;
use Overblog\GraphQLBundle\Definition\GlobalVariables;
use Overblog\GraphQLBundle\Definition\LazyConfig;
use Overblog\GraphQLBundle\Definition\Type\GeneratedTypeInterface;

/**
 * THIS FILE WAS GENERATED AND SHOULD NOT BE MODIFIED!
 */
final class SortType extends InputObjectType implements GeneratedTypeInterface
{
    const NAME = 'Sort';

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'Sort',
            'description' => null,
            'validation' => null,
            'fields' => function () use ($globalVariable) {
                return [
                'sortBy' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => null,
                    # validation is a custom option managed only by the bundle
                    'validation' => [
                    'link' => null,
                    'constraints' => null,
                    'cascade' => null
                ]
                ],
                'sortDirection' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('SortDirection')),
                    'description' => null,
                    # validation is a custom option managed only by the bundle
                    'validation' => [
                    'link' => null,
                    'constraints' => null,
                    'cascade' => null
                ]
                ],
            ];
            },
        ];
        };
        $config = $configProcessor->process(LazyConfig::create($configLoader, $globalVariables))->load();
        parent::__construct($config);
    }
}
