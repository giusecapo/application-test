<?php

namespace App\GraphQL\Mutation;

use App\DocumentModel\EventModel;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use App\Document\Event;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Service\GraphQL\MutationInputReShaper;
use App\Service\Constant\ExceptionCodes;
use App\Service\GraphQL\ValidationHelper;

final class EventMutations implements MutationInterface
{

    public function __construct(
        private EventModel $eventModel,
        private MutationInputReShaper $mutationInputReShaper,
        private ValidationHelper $validationHelper
    ) {
    }

    public function createEvent(array $input): array
    {
        $input = $this->mutationInputReShaper->reShape($input);
        $event = new Event();
        $this->eventModel->getMapper()->map($event, $input);
        $this->validationHelper->validateType($event, ['user_create']);
        $this->eventModel->create($event);
        $this->eventModel->getWriteManager()->flush();

        return [
            'event' => $event
        ];
    }

    public function updateEvent(array $input): array
    {
        $input = $this->mutationInputReShaper->reShape($input);
        $event = $this->eventModel->getRepository()->getById($input['id'], false);
        if (!isset($event)) {
            throw new BadRequestHttpException(
                sprintf('Event with id %s does not exist', $input['id']),
                null,
                ExceptionCodes::BAD_REQUEST_EXCEPTION
            );
        }
        $this->eventModel->getMapper()->map($event, $input);
        $this->validationHelper->validateType($event, ['user_update']);
        $this->eventModel->update($event, null, $input['version']);
        $this->eventModel->getWriteManager()->flush();

        return [
            'event' => $event
        ];
    }
}
