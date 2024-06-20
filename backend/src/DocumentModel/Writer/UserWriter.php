<?php

declare(strict_types=1);

namespace App\DocumentModel\Writer;

use App\Contract\DocumentModel\Writer\WriterInterface;
use App\Document\User;
use App\Service\Document\ReadWriteHelper;

final class UserWriter implements WriterInterface
{

    public const DOCUMENT_NAME = User::class;

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
