<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\DocumentModel\UserModel;
use App\Service\GraphQL\RelayConnection;
use App\Service\GraphQL\RelayConnectionProvider;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use \ArrayObject;
use App\Utility\MethodsBuilder;
use App\Service\GraphQL\RelayConnectionArgumentsProvider;

final class QueryUserConnectionResolver implements ResolverInterface
{
    public function __construct(
        private UserModel $userModel,
        private RelayConnectionArgumentsProvider $relayConnectionArgumentsProvider,
        private RelayConnectionProvider $relayConnectionProvider
    ) {
    }


    public function resolveConnection(ArgumentInterface $args): RelayConnection
    {
        $queryCriteria = $this->relayConnectionArgumentsProvider->toQueryCriteria($args);
        $subsetQueryResult = $this->userModel->getRepository()->getSubset($queryCriteria);
        $relayConnection = $this->relayConnectionProvider->toConnection($subsetQueryResult);
        return $relayConnection;
    }

    /**
     * This magic method is called to resolve each field of the connection type. 
     *
     * @param  RelayConnection $relayConnection
     */
    public function __invoke(ResolveInfo $info, $relayConnection, ArgumentInterface $args, ArrayObject $context): mixed
    {
        $getterMethodForField =  MethodsBuilder::toGetMethod($info->fieldName);
        return $relayConnection->$getterMethodForField();
    }
}
