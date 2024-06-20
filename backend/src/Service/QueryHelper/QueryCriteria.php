<?php

declare(strict_types=1);

namespace App\Service\QueryHelper;

use App\Service\Constant\ExceptionCodes;
use \DomainException as DomainException;

final class QueryCriteria
{

    public const QUERY_STRATEGY_OFFSET_LIMIT = 0;

    public const QUERY_STRATEGY_CURSOR = 1;

    private ?CursorSubsetDescriptor $cursorSubsetDescriptor;

    private ?OffsetAndLimitDescriptor $offsetAndLimitDescriptor;

    private SortingDescriptor $sortingDescriptor;

    private ?FiltersDescriptor $filtersDescriptor;

    private ?UpdateDescriptor $updateDescriptor;

    private array $fieldsToPrime;

    private array $fieldsToSelect;

    private array $fieldsToExclude;

    private bool $readOnly;

    private ?string $distinctField;

    private ?string $sumField;

    /** compute total count or not on subset and cursor queries */
    private bool $totalCount;

    public function __construct()
    {
        $this->cursorSubsetDescriptor = null;
        $this->offsetAndLimitDescriptor = null;
        $this->sortingDescriptor = new SortingDescriptor();
        $this->filtersDescriptor = null;
        $this->updateDescriptor = null;
        $this->fieldsToPrime = array();
        $this->fieldsToSelect = array();
        $this->fieldsToExclude = array();
        $this->readOnly = false;
        $this->distinctField = null;
        $this->sumField = null;
        $this->totalCount = true;
    }

    public function setCursorSubsetDescriptor(CursorSubsetDescriptor $cursorSubsetDescriptor, bool $condition = true): QueryCriteria
    {
        if ($condition) {
            if (isset($this->offsetAndLimitDescriptor)) {
                throw new DomainException(
                    'A query criteria cannot hold both $cursorSubsetDescriptor and $offsetAndLimitDescriptor',
                    ExceptionCodes::DOMAIN_EXCEPTION
                );
            }
            $this->cursorSubsetDescriptor = $cursorSubsetDescriptor;
        }

        return $this;
    }

    public function getCursorSubsetDescriptor(): ?CursorSubsetDescriptor
    {
        return $this->cursorSubsetDescriptor;
    }

    public function setOffsetAndLimitDescriptor(OffsetAndLimitDescriptor $offsetAndLimitDescriptor, bool $condition = true): QueryCriteria
    {
        if ($condition) {
            if (isset($this->cursorSubsetDescriptor)) {
                throw new DomainException(
                    'A query criteria cannot hold both $cursorSubsetDescriptor and $offsetAndLimitDescriptor',
                    ExceptionCodes::DOMAIN_EXCEPTION
                );
            }
            $this->offsetAndLimitDescriptor = $offsetAndLimitDescriptor;
        }
        return $this;
    }

    public function getOffsetAndLimitDescriptor(): ?OffsetAndLimitDescriptor
    {
        return $this->offsetAndLimitDescriptor;
    }

    public function setSortingDescriptor(SortingDescriptor $sortingDescriptor, bool $condition = true): QueryCriteria
    {
        if ($condition) {
            $this->sortingDescriptor = $sortingDescriptor;
        }
        return $this;
    }

    public function getSortingDescriptor(): SortingDescriptor
    {
        return $this->sortingDescriptor;
    }

    public function setFiltersDescriptor(?FiltersDescriptor $filtersDescriptor): QueryCriteria
    {
        $this->filtersDescriptor = $filtersDescriptor;

        return $this;
    }

    public function getFiltersDescriptor(): ?FiltersDescriptor
    {
        return $this->filtersDescriptor;
    }

    public function getFieldsToPrime(): array
    {
        return $this->fieldsToPrime;
    }

    public function setFieldsToPrime(array $fieldsToPrime): QueryCriteria
    {
        $this->fieldsToPrime = $fieldsToPrime;

        return $this;
    }

    public function getFieldsToSelect(): array
    {
        return $this->fieldsToSelect;
    }

    public function setFieldsToSelect(array $fieldsToSelect): QueryCriteria
    {
        $this->fieldsToSelect = $fieldsToSelect;

        return $this;
    }

    public function addFieldToSelect(string $fieldToSelect): QueryCriteria
    {
        $this->fieldsToSelect[] = $fieldToSelect;

        return $this;
    }

    public function getFieldsToExclude(): array
    {
        return $this->fieldsToExclude;
    }

    public function setFieldsToExclude(array $fieldsToExclude): QueryCriteria
    {
        $this->fieldsToExclude = $fieldsToExclude;

        return $this;
    }

    public function addFieldToExclude(string $fieldToExclude): QueryCriteria
    {
        $this->fieldsToExclude[] = $fieldToExclude;

        return $this;
    }


    /**
     * @internal
     * Returns 0 for offset-limit query strategy and 1 for cursor based pagination strategy
     */
    public function getQueryStrategy(): int
    {
        return $this->cursorSubsetDescriptor
            ? static::QUERY_STRATEGY_CURSOR
            : static::QUERY_STRATEGY_OFFSET_LIMIT;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function readOnly(bool $readOnly = true)
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    public function getUpdateDescriptor(): ?UpdateDescriptor
    {
        return $this->updateDescriptor;
    }

    public function setUpdateDescriptor(?UpdateDescriptor $updateDescriptor): self
    {
        $this->updateDescriptor = $updateDescriptor;

        return $this;
    }

    public function getDistinctField(): ?string
    {
        return $this->distinctField;
    }

    public function setDistinctField(?string $distinctField): self
    {
        $this->distinctField = $distinctField;

        return $this;
    }

    public function getSumField(): ?string
    {
        return $this->sumField;
    }

    public function setSumField(?string $sumField): self
    {
        $this->sumField = $sumField;

        return $this;
    }

    public function getTotalCount(): bool
    {
        return $this->totalCount;
    }

    public function setTotalCount(bool $totalCount): self
    {
        $this->totalCount = $totalCount;

        return $this;
    }
}
