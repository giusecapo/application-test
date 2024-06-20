<?php

declare(strict_types=1);

namespace App\Utility;

use App\Service\Constant\ExceptionCodes;
use \InvalidArgumentException as InvalidArgumentException;
use \RecursiveArrayIterator as RecursiveArrayIterator;
use \RecursiveIteratorIterator as RecursiveIteratorIterator;
use function count;
use function array_key_exists;
use function is_bool;
use function is_int;
use function is_float;
use function is_string;
use function is_array;
use function is_object;
use function is_callable;

final class ArrayValidator
{

    /**
     * Check if array is associative array
     */
    public static function isAssoc(array $array): bool
    {
        if (array() === $array) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public static function hasObjArrCallAsChildren(array $array): bool
    {
        foreach ($array as $value) {
            if (is_object($value) || is_array($value) || is_callable($value)) {
                return true;
            }
        }
        return false;
    }

    public static function hasOnlyScalarsAsChildren(array $array): bool
    {
        foreach ($array as $value) {
            if (!is_scalar($value)) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyScalarsAndNullAsChildren(array $array): bool
    {
        foreach ($array as $value) {
            if (!is_scalar($value) && $value !== null) {
                return false;
            }
        }
        return true;
    }


    public static function deepHasOnlyScalarsAsChildren(array $array): bool
    {
        foreach ($array as $value) {
            if (
                (!is_array($value) && !is_scalar($value))
                || (is_array($value) && !ArrayValidator::deepHasOnlyScalarsAsChildren($value))
            ) {
                return false;
            }
        }
        return true;
    }

    public static function deepHasOnlyScalarsAndNullAsChildren(array $array): bool
    {
        foreach ($array as $value) {
            if (
                (!is_array($value) && !is_scalar($value) && $value !== null)
                || (is_array($value) && !ArrayValidator::deepHasOnlyScalarsAndNullAsChildren($value))
            ) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyObjs(array $array): bool
    {
        foreach ($array as $value) {
            if (!is_object($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check the depth (the number of dimensions) of an array
     * @return int the maximum depth of the multidimensional array
     */
    public static function getArrayDepth(array $array): int
    {
        $depth = 0;
        $recIteIte = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

        foreach ($recIteIte as $ite) {
            $tmpDepth = $recIteIte->getDepth();
            $depth = $tmpDepth > $depth
                ? $tmpDepth
                : $depth;
        }
        return $depth;
    }

    public static function hasOnlyStrings(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }
        foreach ($array as $value) {
            if (!is_string($value)) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyNumbers(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }
        foreach ($array as $value) {
            if (
                !is_integer($value)
                && !is_float($value)
            ) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyNumbersNoTypeCheck(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }
        foreach ($array as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyPositiveInt(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }
        foreach ($array as $value) {
            if (!is_int($value) || $value < 0) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyPositiveIntNoTypeCheck(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }
        foreach ($array as $value) {
            if (!is_int((int) $value) || (int) $value < 0) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyInt(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }

        foreach ($array as $value) {
            if (!is_int($value)) {
                return false;
            }
        }

        return true;
    }

    public static function hasOnlyIntNoTypeCheck(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }

        foreach ($array as $value) {
            if (!is_int((int) $value)) {
                return false;
            }
        }

        return true;
    }

    public static function hasOnlyNull(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }

        foreach ($array as $value) {
            if ($value !== null) {
                return false;
            }
        }

        return true;
    }


    /**
     * Check if an array contains all the specified keys
     * @throws InvalidArgumentException if $array is not associative or $keys is not an array of strings
     */
    public static function hasKeys(array $array, array $keys): bool
    {
        if (!self::areValidArrayKeys($keys)) {
            throw new InvalidArgumentException(
                'Only strings and integers are valid array keys.',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }
        foreach ($keys as $value) {
            if (!array_key_exists($value, $array)) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyInstancesOfClass(array $array, string $classNames): bool
    {
        foreach ($array as $value) {
            if (!$value instanceof $classNames) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyInstancesOfClasses(array $array, array $classNames): bool
    {
        foreach ($array as $value) {
            $isInstanceOfClasses = false;
            foreach ($classNames as $className) {
                if ($value instanceof $className) {
                    $isInstanceOfClasses = true;
                }
            }
            if (!$isInstanceOfClasses) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnlyBoolTrue(array $array): bool
    {
        foreach ($array as $value) {
            if (!is_bool($value) || !$value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate each element in an array against a custom callback
     */
    public static function validateElements(array $array, callable $callback): bool
    {
        foreach ($array as $value) {
            if ($callback($value) !== true) {
                return false;
            }
        }
        return true;
    }

    /**
     * areValidArrayKeys: check if the values of an array can be used as array keys
     * @param array $array: the array to check
     * @return bool
     */
    public static function areValidArrayKeys(array $array): bool
    {
        foreach ($array as $value) {
            if (
                !is_int($value)
                && !is_string($value)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * isEqualTo: check if two arrays contain the same data set.
     * This method is not suited to compare arrays of objects.
     *
     * @param  array $array1
     * @param  array $array2
     *
     * @return bool
     */
    public static function isEqualTo(array $array1, array $array2): bool
    {
        return count($array1) == count($array2)
            && array_diff($array1, $array2) === array_diff($array2, $array1);
    }
}
