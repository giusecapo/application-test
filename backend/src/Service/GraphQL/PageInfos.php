<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

final class PageInfos
{

    private bool $hasNextPage;

    private bool $hasPreviousPage;

    private ?string $startCursor;

    private ?string $endCursor;

    private bool $isPaginatingForward;


    public function __construct()
    {
        $this->hasNextPage = false;
        $this->hasPreviousPage = false;
        $this->startCursor = null;
        $this->endCursor = null;
        $this->isPaginatingForward = false;
    }

    public function getHasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    public function setHasNextPage(bool $hasNextPage): PageInfos
    {
        $this->hasNextPage = $hasNextPage;

        return $this;
    }

    public function getHasPreviousPage(): bool
    {
        return $this->hasPreviousPage;
    }

    public function setHasPreviousPage(bool $hasPreviousPage): PageInfos
    {
        $this->hasPreviousPage = $hasPreviousPage;

        return $this;
    }

    public function getStartCursor(): ?string
    {
        return $this->startCursor;
    }

    public function setStartCursor(?string $startCursor): PageInfos
    {
        $this->startCursor = $startCursor;

        return $this;
    }

    public function getEndCursor(): ?string
    {
        return $this->endCursor;
    }

    public function setEndCursor(?string $endCursor): PageInfos
    {
        $this->endCursor = $endCursor;

        return $this;
    }

    public function getIsPaginatingForward(): bool
    {
        return $this->isPaginatingForward;
    }

    public function setIsPaginatingForward(bool $isPaginatingForward): self
    {
        $this->isPaginatingForward = $isPaginatingForward;

        return $this;
    }
}
