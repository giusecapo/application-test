<?php

declare(strict_types=1);

namespace App\DocumentModel\Mapper;

use App\Contract\Document\DocumentInterface;
use App\Contract\Document\EmbeddedDocumentInterface;
use \InvalidArgumentException as InvalidArgumentException;
use App\Contract\DocumentModel\Mapper\MapperInterface;
use App\Document\Speech;
use App\Service\Document\MappingHelper;
use App\Service\Constant\ExceptionCodes;

final class SpeechMapper implements MapperInterface
{

    public const DOCUMENT_NAME = Speech::class;

    public function __construct(private MappingHelper $mappingHelper)
    {
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
    public function map(DocumentInterface|EmbeddedDocumentInterface $speech, array $input): void
    {
        if (!$speech instanceof Speech) {
            throw new InvalidArgumentException(
                sprintf('$speech must be an instance of %s', self::DOCUMENT_NAME),
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        $this->mappingHelper->mapSimple($speech, 'topic', $input);
        $this->mappingHelper->mapSimple($speech, 'speaker', $input);
        $this->mappingHelper->mapSimple($speech, 'startTime', $input);
        $this->mappingHelper->mapSimple($speech, 'endTime', $input);
    }


    /**
     * @inheritDoc
     */
    public function unMap(DocumentInterface|EmbeddedDocumentInterface $speech): array
    {
        if (!$speech instanceof Speech) {
            throw new InvalidArgumentException(
                sprintf('$speech must be an instance of %s', self::DOCUMENT_NAME),
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        return [
            'topic' => $speech->getTopic(),
            'speaker' => $speech->getSpeaker(),
            'startTime' => $speech->getStartTime(),
            'endTime' => $speech->getEndTime(),
        ];
    }
}
