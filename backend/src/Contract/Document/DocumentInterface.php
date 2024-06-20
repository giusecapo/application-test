<?php

declare(strict_types=1);

namespace App\Contract\Document;

/**
 * Classes implementing DocumentInterface 
 * can be used as "top level" persistent entities/documents
 */
interface DocumentInterface
{

    /**
     * Returns the fully qualified class name of the document model 
     * to use for handling the document;
     */
    public static  function getDocumentModelName(): string;

    /**
     * Returns the fully qualified class name of the document 
     */
    public function getDocumentName(): string;

    public function getId(): ?string;

    public function setId(?string $id): self;
}
