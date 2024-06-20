<?php

declare(strict_types=1);

namespace App\Contract\DocumentModel\Repository;

interface RepositoryInterface
{
    /**
     * Returns the name of the document related to this model
     */
    public function getDocumentName(): string;
}
