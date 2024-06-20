<?php

declare(strict_types=1);

namespace App\DocumentModel\Writer;

use App\Contract\Document\DocumentInterface;
use App\Contract\DocumentModel\Writer\WriterInterface;

final class DocumentAwareDocumentWriter implements WriterInterface
{

	public const DOCUMENT_NAME = DocumentInterface::class;

	/**
	 * @inheritDoc
	 */
	public function getDocumentName(): string
	{
		return self::DOCUMENT_NAME;
	}
}
