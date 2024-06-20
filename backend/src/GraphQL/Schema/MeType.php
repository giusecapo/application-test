<?php
namespace App\GraphQL\Schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Overblog\GraphQLBundle\Definition\ConfigProcessor;
use Overblog\GraphQLBundle\Definition\GlobalVariables;
use Overblog\GraphQLBundle\Definition\LazyConfig;
use Overblog\GraphQLBundle\Definition\Type\GeneratedTypeInterface;

/**
 * THIS FILE WAS GENERATED AND SHOULD NOT BE MODIFIED!
 */
final class MeType extends ObjectType implements GeneratedTypeInterface
{
    const NAME = 'Me';

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'Me',
            'description' => null,
            'fields' => function () use ($globalVariable) {
                return [
                'user' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('User'),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => null,
                    'deprecationReason' => null,
                    'complexity' => function ($childrenComplexity, $args = []) use ($globalVariable) {
                        $resolveComplexity = function ($childrenComplexity, $args = []) use ($globalVariable) {
                            return (100 + $childrenComplexity);
                        };

                        return call_user_func_array($resolveComplexity, [$childrenComplexity, $globalVariable->get('argumentFactory')->create($args)]);
                    },
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
                'authenticated' => [
                    'type' => Type::nonNull(Type::boolean()),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => null,
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
                'grantedRoles' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(Type::string()))),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => null,
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
                'isGrantedRole' => [
                    'type' => Type::nonNull(Type::boolean()),
                    'args' => [
                        [
                            'name' => 'role',
                            'type' => Type::nonNull(Type::string()),
                            'description' => null,
                        ],
                    ],
                    'resolve' => null,
                    'description' => null,
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
            ];
            },
            'interfaces' => function () use ($globalVariable) {
                return [];
            },
            'isTypeOf' => null,
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                return $globalVariable->get('resolverResolver')->resolve(["App\\GraphQL\\Resolver\\MeResolver", [0 => $info, 1 => $value, 2 => $args]]);
            },
        ];
        };
        $config = $configProcessor->process(LazyConfig::create($configLoader, $globalVariables))->load();
        parent::__construct($config);
    }
}
