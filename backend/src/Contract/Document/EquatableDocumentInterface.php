<?php

declare(strict_types=1);

namespace App\Contract\Document;

interface EquatableDocumentInterface
{

    /**
     * Verify if the document corresponds to the provided one. 
     * This doesn't mean every attribute should match exactly. 
     * Instead, it's about determining if both documents represent the same 
     * information or piece of data within the domain model.
     * For instance, two User documents with the same username or two positions with 
     * identical geographical coordinates are the same piece of data.
     */
    public function isEqualTo(EquatableDocumentInterface $document): bool;
}
