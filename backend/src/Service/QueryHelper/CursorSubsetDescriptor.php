<?php

declare(strict_types=1);

namespace App\Service\QueryHelper;

use App\Service\Constant\ExceptionCodes;
use App\Service\PaginationCursor\DecodedPaginationCursor;
use \DomainException as DomainException;

final class CursorSubsetDescriptor
{

    private ?int $first;

    private ?DecodedPaginationCursor $after;

    private ?int $last;

    private ?DecodedPaginationCursor $before;


    public function __construct()
    {
        $this->first = null;
        $this->after = null;
        $this->last = null;
        $this->before = null;
    }

    /**
     * @throws InvalidArgumentException if $first is not an int greater than or equal to 1
     */
    public function setFirst(?int $first): CursorSubsetDescriptor
    {
        if (isset($this->last) && isset($first)) {
            throw new DomainException(
                '$first cannot be set because $last is already defined',
                ExceptionCodes::DOMAIN_EXCEPTION
            );
        }

        $this->first = $first;

        return $this;
    }

    public function getFirst(): ?int
    {
        return $this->first;
    }

    public function setAfter(?DecodedPaginationCursor $after): CursorSubsetDescriptor
    {
        $this->after = $after;

        return $this;
    }

    public function getAfter(): ?DecodedPaginationCursor
    {
        return $this->after;
    }

    /**
     * @throws InvalidArgumentException if $last is an int not greater than or equal to 1
     */
    public function setLast(?int $last): CursorSubsetDescriptor
    {
        if (isset($this->first) && isset($last)) {
            throw new DomainException(
                '$last cannot be set because $first is already defined',
                ExceptionCodes::DOMAIN_EXCEPTION
            );
        }

        $this->last = $last;

        return $this;
    }

    public function getLast(): ?int
    {
        return $this->last;
    }

    public function setBefore(?DecodedPaginationCursor $before): CursorSubsetDescriptor
    {
        $this->before = $before;

        return $this;
    }

    public function getBefore(): ?DecodedPaginationCursor
    {
        return $this->before;
    }
}
