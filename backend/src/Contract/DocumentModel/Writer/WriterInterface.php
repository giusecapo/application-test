<?php

declare(strict_types=1);

namespace App\Contract\DocumentModel\Writer;

interface WriterInterface
{
	/**
	 * Returns the name o the document related to this model
	 */
	public function getDocumentName(): string;
}
