<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\DocumentModel\EventModel;
use App\Utility\MethodsBuilder;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use App\Service\GraphQL\LimitOffsetQueryArgumentsProvider;
use App\Service\GraphQL\Subset;
use App\Service\GraphQL\SubsetProvider;
use ArrayObject;

final class EventsSubsetResolver implements ResolverInterface
{

    public function __construct(
        private EventModel $eventModel,
        private LimitOffsetQueryArgumentsProvider $limitOffsetQueryArgumentsProvider,
        private SubsetProvider $subsetProvider
    ) {
    }


    public function resolveSubset(ArgumentInterface $args): Subset
    {
        $queryCriteria = $this->limitOffsetQueryArgumentsProvider->toQueryCriteria($args);
        $subsetQueryResult = $this->eventModel->getRepository()->getSubset($queryCriteria);
        $subset = $this->subsetProvider->toSubset($subsetQueryResult);
        return $subset;
    }

    /**
     * This magic method is called to resolve each field of the connection type. 
     *
     * @param  Subset $subset
     */
    public function __invoke(ResolveInfo $info, $subset, ArgumentInterface $args, ArrayObject $context): mixed
    {
        $getterMethodForField =  MethodsBuilder::toGetMethod($info->fieldName);
        return $subset->$getterMethodForField();
    }
}
