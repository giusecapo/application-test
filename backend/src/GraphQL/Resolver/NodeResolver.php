<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\DocumentModel\DocumentAwareDocumentModel;
use App\Service\GraphQL\Buffer;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use App\Service\GlobalId\GlobalIdProvider;
use GraphQL\Deferred;
use App\Service\GraphQL\GetByFieldValuesQueryArgumentsProvider;
use Doctrine\Common\Collections\Collection;

final class NodeResolver implements ResolverInterface
{

    public function __construct(
        private DocumentAwareDocumentModel $documentAwareDocumentModel,
        private GlobalIdProvider $globalIdProvider,
        private Buffer $buffer,
        private GetByFieldValuesQueryArgumentsProvider $getByFieldValuesQueryArgumentsProvider
    ) {
    }

    public function resolveOneById(string $globalId): Deferred
    {
        $decodedGlobalId = $this->globalIdProvider->fromGlobalId($globalId);
        $documentName = $decodedGlobalId->getDocumentName();
        $id = $decodedGlobalId->getId();

        $buffer = $this->buffer;
        $model = $this->documentAwareDocumentModel;
        $getByFieldValuesQueryArgumentsProvider = $this->getByFieldValuesQueryArgumentsProvider;
        $buffer->add($documentName, 'id', $id);

        return new Deferred(fn () => $buffer->get(
            $documentName,
            'id',
            $id,
            function ($ids) use ($model, $documentName, $getByFieldValuesQueryArgumentsProvider): Collection {
                $queryCriteria = $getByFieldValuesQueryArgumentsProvider->toQueryCriteria('id', $ids);
                return $model->getRepository()->find($documentName, $queryCriteria);
            }
        ));
    }
}
