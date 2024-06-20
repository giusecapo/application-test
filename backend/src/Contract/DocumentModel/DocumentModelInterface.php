<?php

declare(strict_types=1);

namespace App\Contract\DocumentModel;

use App\Contract\DocumentModel\Repository\RepositoryInterface;
use App\Contract\DocumentModel\Writer\WriterInterface;
use App\Service\Document\WriteManager;

interface DocumentModelInterface
{
    /**
     * Returns the name o the document related to this model
     */
    public function getDocumentName(): string;


    /**
     * Returns the repository to use to query for documents
     */
    public function getRepository(): RepositoryInterface;

    /**
     * Returns the write manager which exposes the required methods 
     * to handle documents in the unit of work and flush changes
     */
    public function getWriteManager(): WriteManager;

    /**
     * Returns the writer for the model
     */
    public function getWriter(): WriterInterface;
}
