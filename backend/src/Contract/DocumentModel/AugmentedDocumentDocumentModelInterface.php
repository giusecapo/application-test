<?php

declare(strict_types=1);

namespace App\Contract\DocumentModel;

use App\Contract\DocumentModel\Mapper\MapperInterface;

interface AugmentedDocumentDocumentModelInterface extends DocumentModelInterface
{
	/**
	 * Returns the document mapper to use to map data in a document
	 * and transform a document's data in an associative array
	 */
	public function getMapper(): MapperInterface;
}
