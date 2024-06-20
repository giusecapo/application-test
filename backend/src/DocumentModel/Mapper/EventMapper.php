<?php

declare(strict_types=1);

namespace App\DocumentModel\Mapper;

use App\Contract\Document\DocumentInterface;
use App\Contract\Document\EmbeddedDocumentInterface;
use App\Document\User;
use \InvalidArgumentException as InvalidArgumentException;
use App\Contract\DocumentModel\Mapper\MapperInterface;
use App\Document\Event;
use App\Document\Speech;
use App\Service\Document\MappingHelper;
use App\Service\Constant\ExceptionCodes;
use DateTimeInterface;

final class EventMapper implements MapperInterface
{

    public const DOCUMENT_NAME = Event::class;

    public function __construct(
        private SpeechMapper $speechMapper,
        private MappingHelper $mappingHelper
    ) {
    }


    /**
     * @inheritDoc
     */
    public function getDocumentName(): string
    {
        return self::DOCUMENT_NAME;
    }


    /**
     * @inheritDoc
     */
    public function map(DocumentInterface|EmbeddedDocumentInterface $event, array $input): void
    {
        if (!$event instanceof Event) {
            throw new InvalidArgumentException(
                sprintf('$event must be an instance of %s', self::DOCUMENT_NAME),
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        $this->mappingHelper->mapSimple($event, 'id', $input);
        $this->mappingHelper->mapSimple($event, 'version', $input);
        $this->mappingHelper->mapSimple($event, 'key', $input);
        $this->mappingHelper->mapSimple($event, 'name', $input);
        $this->mappingHelper->mapSimple($event, 'participants', $input);
        $this->mappingHelper->mapDateTime($event, 'date', $input);
        $this->mappingHelper->mapArrayCollectionOfEmbeddedDocuments($event, 'program', $input, $this->speechMapper);
    }


    /**
     * @inheritDoc
     */
    public function unMap(DocumentInterface|EmbeddedDocumentInterface $event): array
    {
        if (!$event instanceof Event) {
            throw new InvalidArgumentException(
                sprintf('$event must be an instance of %s', self::DOCUMENT_NAME),
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        return [
            'id' => $event->getId(),
            'version' => $event->getVersion(),
            'key' => $event->getKey(),
            'name' => $event->getName(),
            'participants' => $event->getParticipants(),
            'date' => $event->getDate()->format(DateTimeInterface::ATOM),
            'program' => array_map(
                fn (Speech $speech): array => $this->speechMapper->unMap($speech),
                $event->getProgram()->toArray()
            )
        ];
    }
}
