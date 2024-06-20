<?php

declare(strict_types=1);

namespace App\Service\GlobalId;


final class DecodedGlobalId
{
    private string $id;

    private string $documentName;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): DecodedGlobalId
    {
        $this->id = $id;

        return $this;
    }

    public function getDocumentName(): string
    {
        return $this->documentName;
    }

    public function setDocumentName(string $documentName): DecodedGlobalId
    {
        $this->documentName = $documentName;

        return $this;
    }
}
