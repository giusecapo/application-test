<?php

declare(strict_types=1);

namespace App\DocumentModel\Writer;

use App\Contract\DocumentModel\Writer\WriterInterface;
use App\Document\Event;
use App\Service\Document\ReadWriteHelper;

final class EventWriter implements WriterInterface
{

    public const DOCUMENT_NAME = Event::class;

    public function __construct(private ReadWriteHelper $readWriteHelper)
    {
    }

    /**
     * @inheritDoc
     */
    public function getDocumentName(): string
    {
        return self::DOCUMENT_NAME;
    }
}
