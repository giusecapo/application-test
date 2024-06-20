<?php

declare(strict_types=1);

namespace App\Contract\Document;

interface IdenticalDocumentInterface
{

    /**
     * Ensure that all attributes of the document match those of the provided one.
     * Exceptions can be made for values that are random or auto-generated, such as IDs, 
     * and for those values whose variations don't impact the business model.
     * For instance, two User documents might be identical in all aspects except for their IDs.
     */
    public function isIdenticalTo(IdenticalDocumentInterface $document): bool;
}
