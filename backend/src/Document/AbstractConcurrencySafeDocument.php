<?php

declare(strict_types=1);

namespace App\Document;

use App\Contract\Document\ConcurrencySafeDocumentInterface;
use App\Contract\Document\DocumentInterface;

abstract class AbstractConcurrencySafeDocument implements
    DocumentInterface,
    ConcurrencySafeDocumentInterface
{
    protected ?string $id = null;

    protected ?int $version = null;

    /**
     * @inheritDoc
     */
    abstract public static  function getDocumentModelName(): string;

    /**
     * @inheritDoc
     */
    abstract public function getDocumentName(): string;

    /**
     * @inheritDoc
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): ?int
    {
        return $this->version;
    }


    /**
     * @inheritDoc
     */
    public function setVersion(?int $version): self
    {
        $this->version = $version;

        return $this;
    }
}
