<?php

declare(strict_types=1);

namespace App\Contract\Document;

interface LocaleInterface extends EmbeddedDocumentInterface
{

    public function getLocaleCode(): ?string;

    public function getWeight(): int;
}
