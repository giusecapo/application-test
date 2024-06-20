<?php

declare(strict_types=1);

namespace App\Service\QueryHelper;

final class UpdateDescriptor
{

    /**
     * This array will collect all the update operations to apply.
     * The associative array is formatted as follows:
     * [ 
     *  'field' => 'fieldName' //the name of the object's field. e.g. username
     *  'operator' => 'operatorName', // e.g set, inc, push
     *  'value' => 'value' //mixed (e.g. an array, a string, an int, float ...)
     * ]
     */
    private array $updateOperations;


    public function __construct()
    {
        $this->updateOperations = array();
    }

    public function set(string $field, mixed $value, bool $condition = true): UpdateDescriptor
    {
        if ($condition) {
            $this->updateOperations[] = [
                'field' => $field,
                'operator' => 'set',
                'value' => $value
            ];
        }
        return $this;
    }

    public function push(string $field, mixed $value, bool $condition = true): UpdateDescriptor
    {
        if ($condition) {
            $this->updateOperations[] = [
                'field' => $field,
                'operator' => 'push',
                'value' => $value
            ];
        }
        return $this;
    }

    public function increment(string $field, int|float $value, bool $condition = true): UpdateDescriptor
    {
        if ($condition) {
            $this->updateOperations[] = [
                'field' => $field,
                'operator' => 'inc',
                'value' => $value
            ];
        }
        return $this;
    }

    /**
     * Returns the update operations formatted as an array
     * The array entries have following shape:
     * [ 
     *  'field' => 'fieldName' //the name of the object's field. e.g. username
     *  'operator' => 'operatorName', // e.g set, inc, push
     *  'value' => 'value' //mixed (e.g. an array, a string, an int, float ...)
     * ]
     */
    public function getUpdateOperationsAsArray(): array
    {
        return $this->updateOperations;
    }
}
