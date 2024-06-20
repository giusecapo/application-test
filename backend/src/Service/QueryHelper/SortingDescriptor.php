<?php

declare(strict_types=1);

namespace App\Service\QueryHelper;

use App\Service\Constant\ExceptionCodes;
use \InvalidArgumentException as InvalidArgumentException;


final class SortingDescriptor
{

    public const ASC = 1;
    public const DESC = -1;

    private string $sortBy;

    private int $sortDirection;


    public function __construct()
    {
        //by default we want the newest records
        $this->sortBy = 'id';
        $this->sortDirection = static::DESC;
    }

    public function setSortBy(?string $sortBy): SortingDescriptor
    {
        if (isset($sortBy)) {
            $this->sortBy = $sortBy;
        }
        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    /**
     * @throws InvalidArgumentException if $sortDirection is not an int equal to 1 or -1
     */
    public function setSortDirection(?int $sortDirection): SortingDescriptor
    {
        if (isset($sortDirection) && $sortDirection !== static::ASC && $sortDirection !== static::DESC) {
            throw new InvalidArgumentException(
                '$sortDirection must be set to 1 for \'asc\' or -1 for \'desc\'.',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }
        if (isset($sortDirection)) {
            $this->sortDirection = $sortDirection;
        }
        return $this;
    }

    public function getSortDirection(): int
    {
        return $this->sortDirection;
    }
}
