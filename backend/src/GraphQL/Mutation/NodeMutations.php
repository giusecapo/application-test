<?php

declare(strict_types=1);

namespace App\GraphQL\Mutation;

use App\DocumentModel\DocumentAwareDocumentModel;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use App\Service\GlobalId\GlobalIdProvider;
use App\Service\GraphQL\FieldEncryptionProvider;
use App\Service\Constant\ExceptionCodes;
use App\Utility\ArrayManipulator;
use \Exception as Exception;

final class NodeMutations implements MutationInterface
{
    public function __construct(
        private DocumentAwareDocumentModel $documentAwareDocumentModel,
        private GlobalIdProvider $globalIdProvider,
        private FieldEncryptionProvider $fieldEncryptionProvider,
    ) {
    }



    /**
     * @param  array $input (type: DeleteNodeInput - the document's ids and versions)
     */
    public function deleteNode(array $input, ?string $reCaptchaToken): array
    {
        $decodedGlobalId = $this->globalIdProvider->fromGlobalId($input['id']);
        $version = isset($input['version'])
            ? (int)$this->fieldEncryptionProvider->decrypt($input['version'], $decodedGlobalId->getId())
            : null;

        $document = $this->documentAwareDocumentModel->getRepository()->getById(
            $decodedGlobalId->getDocumentName(),
            $decodedGlobalId->getId(),
            false
        );
        if (!isset($document)) {
            throw new BadRequestHttpException(
                sprintf('Document with id %s does not exist', $input['id']),
                null,
                ExceptionCodes::BAD_REQUEST_EXCEPTION
            );
        }
        $this->documentAwareDocumentModel->delete($document, $version);
        $this->documentAwareDocumentModel->getWriteManager()->flush();

        return [
            'id' => $input['id']
        ];
    }

    /**
     * @param  array $input (array of DeleteNodeInput - the document's ids and versions)
     */
    public function deleteNodes(array $input, ?string $reCaptchaToken): array
    {
        $deleteNodeInputs = array();

        foreach ($input as $deleteNodeInput) {
            $decodedGlobalId = $this->globalIdProvider->fromGlobalId($deleteNodeInput['id']);
            $version = isset($deleteNodeInput['version'])
                ? (int)$this->fieldEncryptionProvider->decrypt($deleteNodeInput['version'], $decodedGlobalId->getId())
                : null;

            $deleteNodeInputs[] = array(
                'documentName' => $decodedGlobalId->getDocumentName(),
                'id' => $decodedGlobalId->getId(),
                'version' => $version,
                'document' => null
            );
        }

        $arrayManipulator = new ArrayManipulator($deleteNodeInputs);
        $deleteNodeInputsGroupedByDocumentName = $arrayManipulator
            ->groupBySubarrayValue('documentName')
            ->get();


        $documents = array();

        foreach ($deleteNodeInputsGroupedByDocumentName as $deleteNodeInputGroup) {
            $documentName = $deleteNodeInputGroup[0]['documentName'];
            $ids = array_map(
                fn ($deleteNodeInput) => $deleteNodeInput['id'],
                $deleteNodeInputGroup
            );

            $documents = array_merge(
                $documents,
                $this->documentAwareDocumentModel->getRepository()->getByIds($documentName, $ids, false)->toArray()
            );
        }

        foreach ($documents as $document) {
            $deleteNodeInput = array_filter(
                $deleteNodeInputs,
                fn ($deleteNodeInput) => $deleteNodeInput['id'] === $document->getId()
            );
            $version = reset($deleteNodeInput)['version'];

            $this->documentAwareDocumentModel->delete($document, $version);
        }

        try {
            $this->documentAwareDocumentModel->getWriteManager()->flush([], true);
        } catch (Exception) {
            //transaction rolled back: nothing was deleted
            return array();
        }

        //return the ids of the deleted documents
        return array_map(
            fn ($deleteNodeInput) => ['id' => $deleteNodeInput['id']],
            $deleteNodeInputs
        );
    }
}
