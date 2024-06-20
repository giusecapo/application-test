<?php

declare(strict_types=1);

namespace App\Contract\Event;

use App\Contract\Document\DocumentInterface;

interface DocumentEventInterface
{

    /**
     * Returns the document on which the event was triggered
     */
    public function getDocument(): DocumentInterface;

    /**
     * Returns the fully qualified class name of the document on which the event was triggered
     */
    public function getDocumentName(): string;
}
