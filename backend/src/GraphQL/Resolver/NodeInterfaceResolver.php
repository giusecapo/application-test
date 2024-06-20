<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\Contract\Document\DocumentInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use \Exception as Exception;
use GraphQL\Type\Definition\ObjectType;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use Overblog\GraphQLBundle\Resolver\UnresolvableException;

final class NodeInterfaceResolver implements ResolverInterface
{
    public function resolveType(DocumentInterface $document, TypeResolver $typeResolver): ObjectType
    {
        try {
            //This code assumes the GraphQL type has the same name as the document
            //e.g GraphQL type = User; document = App\Document\User.
            $explodedDocumentName = explode('\\', $document->getDocumentName());
            $type = array_pop($explodedDocumentName);
            return $typeResolver->resolve($type);
        } catch (Exception $exception) {
            throw new UnresolvableException(
                "Cannot resolve the type for NodeInterface interface: 
                maybe the type $type does not exists?",
                0,
                $exception
            );
        }
    }
}
