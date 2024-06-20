<?php

declare(strict_types=1);

namespace App\Event;

use App\Contract\Event\DocumentEventInterface;
use App\Contract\Document\DocumentInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched before the update of a document.
 * The event is dispatched just before the unit of work is flushed.
 */
final class DocumentBeforeUpdatedEvent extends Event implements DocumentEventInterface
{

    /**
     * @inheritDoc
     */
    public function __construct(private DocumentInterface $document)
    {
    }

    /**
     * @inheritDoc
     */
    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentName(): string
    {
        return $this->document->getDocumentName();
    }
}
