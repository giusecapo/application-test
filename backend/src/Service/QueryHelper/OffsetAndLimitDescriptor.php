<?php

declare(strict_types=1);

namespace App\Service\QueryHelper;

use App\Service\Constant\ExceptionCodes;
use \InvalidArgumentException as InvalidArgumentException;

final class OffsetAndLimitDescriptor
{

    private ?int $offset;

    private ?int $limit;


    public function __construct()
    {
        $this->offset = null;
        $this->limit = null;
    }


    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @throws InvalidArgumentException if $limit is not an int greater than or equal to 1
     */
    public function setLimit(?int $limit): OffsetAndLimitDescriptor
    {
        if (isset($limit) && $limit < 1) {
            throw new InvalidArgumentException(
                '$limit must be an integer greater than or equal to 1.',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }
        $this->limit = $limit;

        return $this;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): OffsetAndLimitDescriptor
    {
        if (isset($offset) && $offset < 0) {
            throw new InvalidArgumentException(
                'The value of $offset must be greater than or equal to 0',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }
        $this->offset = $offset;

        return $this;
    }
}
