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
final class MutationType extends ObjectType implements GeneratedTypeInterface
{
    const NAME = 'Mutation';

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'Mutation',
            'description' => null,
            'fields' => function () use ($globalVariable) {
                return [
                'deleteNode' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('DeleteNodePayload')),
                    'args' => [
                        [
                            'name' => 'input',
                            'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('DeleteNodeInput')),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('mutationResolver')->resolve(["App\\GraphQL\\Mutation\\NodeMutations::deleteNode", [0 => $args["input"]]]);
                    },
                    'description' => 'Delete a document by it\'s id',
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
                'deleteNodes' => [
                    'type' => Type::nonNull(Type::listOf($globalVariable->get('typeResolver')->resolve('DeleteNodePayload'))),
                    'args' => [
                        [
                            'name' => 'input',
                            'type' => Type::nonNull(Type::listOf(Type::nonNull($globalVariable->get('typeResolver')->resolve('DeleteNodeInput')))),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('mutationResolver')->resolve(["App\\GraphQL\\Mutation\\NodeMutations::deleteNodes", [0 => $args["input"]]]);
                    },
                    'description' => 'Delete a set of documents by its ids',
                    'deprecationReason' => null,
                    'complexity' => function ($childrenComplexity, $args = []) use ($globalVariable) {
                        $resolveComplexity = function ($childrenComplexity, $args = []) use ($globalVariable) {
                            return (600 + $childrenComplexity);
                        };

                        return call_user_func_array($resolveComplexity, [$childrenComplexity, $globalVariable->get('argumentFactory')->create($args)]);
                    },
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
                'createUser' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('UserPayload')),
                    'args' => [
                        [
                            'name' => 'input',
                            'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('CreateUserInput')),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('mutationResolver')->resolve(["App\\GraphQL\\Mutation\\UserMutations::createUser", [0 => $args["input"]]]);
                    },
                    'description' => 'Create a new system user',
                    'deprecationReason' => null,
                    'complexity' => function ($childrenComplexity, $args = []) use ($globalVariable) {
                        $resolveComplexity = function ($childrenComplexity, $args = []) use ($globalVariable) {
                            return (300 + $childrenComplexity);
                        };

                        return call_user_func_array($resolveComplexity, [$childrenComplexity, $globalVariable->get('argumentFactory')->create($args)]);
                    },
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
                'updateUser' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('UserPayload')),
                    'args' => [
                        [
                            'name' => 'input',
                            'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('UpdateUserInput')),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('mutationResolver')->resolve(["App\\GraphQL\\Mutation\\UserMutations::updateUser", [0 => $args["input"]]]);
                    },
                    'description' => 'Update an existing system user',
                    'deprecationReason' => null,
                    'complexity' => function ($childrenComplexity, $args = []) use ($globalVariable) {
                        $resolveComplexity = function ($childrenComplexity, $args = []) use ($globalVariable) {
                            return (300 + $childrenComplexity);
                        };

                        return call_user_func_array($resolveComplexity, [$childrenComplexity, $globalVariable->get('argumentFactory')->create($args)]);
                    },
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
                'createEvent' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('EventPayload')),
                    'args' => [
                        [
                            'name' => 'input',
                            'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('CreateEventInput')),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('mutationResolver')->resolve(["App\\GraphQL\\Mutation\\EventMutations::createEvent", [0 => $args["input"]]]);
                    },
                    'description' => 'Create a new system event',
                    'deprecationReason' => null,
                    'complexity' => function ($childrenComplexity, $args = []) use ($globalVariable) {
                        $resolveComplexity = function ($childrenComplexity, $args = []) use ($globalVariable) {
                            return (300 + $childrenComplexity);
                        };

                        return call_user_func_array($resolveComplexity, [$childrenComplexity, $globalVariable->get('argumentFactory')->create($args)]);
                    },
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                    'useStrictAccess' => true,
                ],
                'updateEvent' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('EventPayload')),
                    'args' => [
                        [
                            'name' => 'input',
                            'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('UpdateEventInput')),
                            'description' => null,
                        ],
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('mutationResolver')->resolve(["App\\GraphQL\\Mutation\\EventMutations::updateEvent", [0 => $args["input"]]]);
                    },
                    'description' => 'Update an existing system event',
                    'deprecationReason' => null,
                    'complexity' => function ($childrenComplexity, $args = []) use ($globalVariable) {
                        $resolveComplexity = function ($childrenComplexity, $args = []) use ($globalVariable) {
                            return (300 + $childrenComplexity);
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
