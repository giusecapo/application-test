<?php

declare(strict_types=1);

namespace App\Service\PaginationCursor;

final class DecodedPaginationCursor
{

    private string $id;

    private mixed $cursorValue;


    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): DecodedPaginationCursor
    {
        $this->id = $id;

        return $this;
    }

    public function getCursorValue(): mixed
    {
        return $this->cursorValue;
    }

    public function setCursorValue(mixed $cursorValue): DecodedPaginationCursor
    {
        $this->cursorValue = $cursorValue;

        return $this;
    }
}
