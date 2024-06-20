<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Contract\Document\DocumentInterface;

final class Edge
{
    private DocumentInterface $node;

    private string $cursor;

    public function getNode(): ?DocumentInterface
    {
        return $this->node;
    }

    public function setNode(DocumentInterface $node): self
    {
        $this->node = $node;

        return $this;
    }

    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    public function setCursor(string $cursor): self
    {
        $this->cursor = $cursor;

        return $this;
    }
}
