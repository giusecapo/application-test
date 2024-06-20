<?php

declare(strict_types=1);

namespace App\DocumentModel;

use App\Contract\Document\DocumentInterface;
use App\Contract\DocumentModel\DocumentModelInterface;
use App\Contract\DocumentModel\Repository\RepositoryInterface;
use App\Contract\DocumentModel\Writer\WriterInterface;
use App\DocumentModel\Writer\DocumentAwareDocumentWriter;
use App\DocumentModel\Repository\DocumentAwareDocumentRepository;
use App\Service\Document\ReadWriteHelper;
use App\Service\Document\WriteManager;

/**
 * DocumentAwareDocumentModel allows the retrieval and deletion of any document
 * implementing DocumentInterface from storage by id (or ids).
 */
final class DocumentAwareDocumentModel implements DocumentModelInterface
{

    public const DOCUMENT_NAME = DocumentInterface::class;

    public function __construct(
        private DocumentAwareDocumentRepository $documentAwareDocumentRepository,
        private DocumentAwareDocumentWriter $documentAwareDocumentWriter,
        private ReadWriteHelper $readWriteHelper,
        private WriteManager $writeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDocumentName(): string
    {
        return self::DOCUMENT_NAME;
    }


    /**
     * @inheritDoc
     * @return DocumentAwareDocumentRepository
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->documentAwareDocumentRepository;
    }

    /**
     * @inheritDoc
     */
    public function getWriter(): WriterInterface
    {
        return $this->documentAwareDocumentWriter;
    }

    /**
     * @inheritDoc
     */
    public function getWriteManager(): WriteManager
    {
        return $this->writeManager;
    }

    public function delete(
        DocumentInterface $document,
        ?int $expectedVersion,
        bool $checkAuthorization = true
    ): void {
        $this->readWriteHelper->delete($document, $expectedVersion, $checkAuthorization);
    }
}
