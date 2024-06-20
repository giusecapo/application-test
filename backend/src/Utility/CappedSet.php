<?php

namespace App\Utility;

use \InvalidArgumentException as InvalidArgumentException;
use function count;

final class CappedSet
{

    private int $maxSize;


    /**
     * This array is used to store keys and lookup if a key is set.
     * In this array we always store true as value, so we can use isset() on the array
     * to check if a key exists and can avoid the expensive use of array_key_exists.
     */
    private array $keys;

    /**
     * Stores the actual values
     */
    private array $cappedSet;

    public function __construct(int $maxSize)
    {
        $this->maxSize = $maxSize > 0 ? $maxSize : 1;
        $this->cappedSet = [];
        $this->keys = [];
    }

    public function add($key, $value): void
    {
        if (count($this->cappedSet) >= $this->maxSize) {
            array_shift($this->cappedSet);
            array_shift($this->keys);
        }

        $this->cappedSet[$key] = $value;
        $this->keys[$key] = true;
    }

    public function isset($key): bool
    {
        return isset($this->keys[$key]);
    }

    public function get($key)
    {
        if (!isset($this->keys[$key])) {
            throw new InvalidArgumentException(
                sprintf('The stack does not contain any value for the key %s', $key)
            );
        }
        return $this->cappedSet[$key];
    }

    public function clearSet(): void
    {
        $this->cappedSet = [];
        $this->keys = [];
    }
}
