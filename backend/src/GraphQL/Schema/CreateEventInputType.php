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
final class CreateEventInputType extends InputObjectType implements GeneratedTypeInterface
{
    const NAME = 'CreateEventInput';

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'CreateEventInput',
            'description' => null,
            'validation' => null,
            'fields' => function () use ($globalVariable) {
                return [
                'key' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => null,
                    # validation is a custom option managed only by the bundle
                    'validation' => [
                    'link' => null,
                    'constraints' => null,
                    'cascade' => null
                ]
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => null,
                    # validation is a custom option managed only by the bundle
                    'validation' => [
                    'link' => null,
                    'constraints' => null,
                    'cascade' => null
                ]
                ],
                'date' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('DateTime')),
                    'description' => null,
                    # validation is a custom option managed only by the bundle
                    'validation' => [
                    'link' => null,
                    'constraints' => null,
                    'cascade' => null
                ]
                ],
                'participants' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(Type::string()))),
                    'description' => null,
                    # validation is a custom option managed only by the bundle
                    'validation' => [
                    'link' => null,
                    'constraints' => null,
                    'cascade' => null
                ]
                ],
                'program' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($globalVariable->get('typeResolver')->resolve('SpeechInput')))),
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
