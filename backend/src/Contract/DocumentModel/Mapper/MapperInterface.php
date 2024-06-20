<?php

declare(strict_types=1);

namespace App\Contract\DocumentModel\Mapper;

use App\Contract\Document\DocumentInterface;
use App\Contract\Document\EmbeddedDocumentInterface;

interface MapperInterface
{

    /**
     * Returns the name o the document related to this model
     */
    public function getDocumentName(): string;

    /**
     * Map the given data ($input) in the given document ($document)
     * @throws InvalidArgumentException if $document is not an instance of the class handled by the model
     */
    public function map(DocumentInterface|EmbeddedDocumentInterface $document, array $input): void;


    /**
     * Convert the document's data in a multi-dimensional array
     * @throws InvalidArgumentException if $document is not an instance of the class handled by the model
     */
    public function unMap(DocumentInterface|EmbeddedDocumentInterface $document): array;
}
