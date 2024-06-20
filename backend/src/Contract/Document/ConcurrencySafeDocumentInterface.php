<?php

declare(strict_types=1);

namespace App\Contract\Document;

interface ConcurrencySafeDocumentInterface extends DocumentInterface
{
    public function getVersion(): ?int;

    public function setVersion(?int $version): self;
}
