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
final class QueryType extends ObjectType implements GeneratedTypeInterface
{
    const NAME = 'Query';

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'Query',
            'description' => null,
            'fields' => function () use ($globalVariable) {
                return [
                'me' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('Me')),
                    'args' => [
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('resolverResolver')->resolve(["App\\GraphQL\\Resolver\\MeResolver::resolveMe", []]);
                    },
                    'description' => 'Returns information about the current user',
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
                'node' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('NodeInterface'),
                    'args' => [
                        [
                            'name' => 'id',
                            'type' => Type::nonNull(Type::id()),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('resolverResolver')->resolve(["App\\GraphQL\\Resolver\\NodeResolver::resolveOneById", [0 => $args["id"]]]);
                    },
                    'description' => 'Returns any type implementing NodeInterface by it\'s global ID',
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
                'user' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('User'),
                    'args' => [
                        [
                            'name' => 'username',
                            'type' => Type::nonNull(Type::string()),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('resolverResolver')->resolve(["App\\GraphQL\\Resolver\\UserResolver::resolveOneByUsername", [0 => $args["username"]]]);
                    },
                    'description' => 'A system user',
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
                'usersSubset' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('UsersSubset')),
                    'args' => [
                        [
                            'name' => 'limit',
                            'type' => Type::nonNull(Type::int()),
                            'description' => null,
                        ],
                        [
                            'name' => 'offset',
                            'type' => Type::nonNull(Type::int()),
                            'description' => null,
                        ],
                        [
                            'name' => 'sort',
                            'type' => $globalVariable->get('typeResolver')->resolve('Sort'),
                            'description' => null,
                        ],
                        [
                            'name' => 'filters',
                            'type' => Type::listOf($globalVariable->get('typeResolver')->resolve('Filter')),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('resolverResolver')->resolve(["App\\GraphQL\\Resolver\\UsersSubsetResolver::resolveSubset", [0 => $args]]);
                    },
                    'description' => 'Paginate through users. Optionally filter the data set.',
                    'deprecationReason' => null,
                    'complexity' => function ($childrenComplexity, $args = []) use ($globalVariable) {
                        $resolveComplexity = function ($childrenComplexity, $args = []) use ($globalVariable) {
                            return (200 + $childrenComplexity);
                        };

                        return call_user_func_array($resolveComplexity, [$childrenComplexity, $globalVariable->get('argumentFactory')->create($args)]);
                    },
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
                'queryUserConnection' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('QueryUserConnection')),
                    'args' => [
                        [
                            'name' => 'first',
                            'type' => Type::int(),
                            'description' => null,
                        ],
                        [
                            'name' => 'after',
                            'type' => Type::string(),
                            'description' => null,
                        ],
                        [
                            'name' => 'last',
                            'type' => Type::int(),
                            'description' => null,
                        ],
                        [
                            'name' => 'before',
                            'type' => Type::string(),
                            'description' => null,
                        ],
                        [
                            'name' => 'sort',
                            'type' => $globalVariable->get('typeResolver')->resolve('Sort'),
                            'description' => null,
                        ],
                        [
                            'name' => 'filters',
                            'type' => Type::listOf($globalVariable->get('typeResolver')->resolve('Filter')),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('resolverResolver')->resolve(["App\\GraphQL\\Resolver\\QueryUserConnectionResolver::resolveConnection", [0 => $args]]);
                    },
                    'description' => 'Paginate through users. Optionally filter the data set.',
                    'deprecationReason' => null,
                    'complexity' => function ($childrenComplexity, $args = []) use ($globalVariable) {
                        $resolveComplexity = function ($childrenComplexity, $args = []) use ($globalVariable) {
                            return (200 + $childrenComplexity);
                        };

                        return call_user_func_array($resolveComplexity, [$childrenComplexity, $globalVariable->get('argumentFactory')->create($args)]);
                    },
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
                'event' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('Event'),
                    'args' => [
                        [
                            'name' => 'key',
                            'type' => Type::nonNull(Type::string()),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('resolverResolver')->resolve(["App\\GraphQL\\Resolver\\EventResolver::resolveOneByKey", [0 => $args["key"]]]);
                    },
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
                'eventsSubset' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('EventsSubset')),
                    'args' => [
                        [
                            'name' => 'limit',
                            'type' => Type::nonNull(Type::int()),
                            'description' => null,
                        ],
                        [
                            'name' => 'offset',
                            'type' => Type::nonNull(Type::int()),
                            'description' => null,
                        ],
                        [
                            'name' => 'sort',
                            'type' => $globalVariable->get('typeResolver')->resolve('Sort'),
                            'description' => null,
                        ],
                        [
                            'name' => 'filters',
                            'type' => Type::listOf($globalVariable->get('typeResolver')->resolve('Filter')),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('resolverResolver')->resolve(["App\\GraphQL\\Resolver\\EventsSubsetResolver::resolveSubset", [0 => $args]]);
                    },
                    'description' => 'Paginate through events. Optionally filter the data set.',
                    'deprecationReason' => null,
                    'complexity' => function ($childrenComplexity, $args = []) use ($globalVariable) {
                        $resolveComplexity = function ($childrenComplexity, $args = []) use ($globalVariable) {
                            return (200 + $childrenComplexity);
                        };

                        return call_user_func_array($resolveComplexity, [$childrenComplexity, $globalVariable->get('argumentFactory')->create($args)]);
                    },
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
            'resolveField' => null,
        ];
        };
        $config = $configProcessor->process(LazyConfig::create($configLoader, $globalVariables))->load();
        parent::__construct($config);
    }
}
