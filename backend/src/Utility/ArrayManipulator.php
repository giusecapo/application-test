<?php

declare(strict_types=1);

namespace App\Utility;

use App\Contract\Document\EquatableDocumentInterface;
use App\Contract\Document\IdenticalDocumentInterface;
use App\Service\Constant\ExceptionCodes;
use \DateTime as DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use \InvalidArgumentException as InvalidArgumentException;
use \Exception as Exception;
use \Error as Error;
use \ReflectionException as ReflectionException;
use function count;
use function in_array;
use function array_key_exists;
use function is_bool;
use function is_int;
use function is_float;
use function is_string;
use function is_array;
use function is_object;
use function strval;
use function gettype;
use function array_slice;

final class ArrayManipulator
{

    public function __construct(private array $array)
    {
    }


    /**
     * @param  callable $callback: receives two arguments; the first one is the array item, the second one is its original key (int|string)
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function map(callable $callback, bool $condition = true): self
    {
        if ($condition) {
            $this->array = array_map($callback, $this->array, array_keys($this->array));
        }
        return $this;
    }

    /**
     * Like array_map() for multi-dimensional arrays
     *
     * @param callable $callback
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function deepMap(callable $callback, bool $condition = true): self
    {
        if ($condition) {
            $mappedArray = array();
            foreach ($this->array as $key => $value) {
                if (is_array($value)) {
                    $arrayManipulator = new ArrayManipulator($value);
                    $mappedArray[$key] = $arrayManipulator->deepMap($callback)->get();
                } else {
                    $mappedArray[$key]  = $callback($value);
                }
            }
            $this->array =  $mappedArray;
        }
        return $this;
    }


    /**
     * @param  callable $callback
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filter(callable $callback, bool $condition = true): self
    {
        if ($condition) {
            $this->array = array_filter($this->array, $callback);
        }
        return $this;
    }


    /**
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeDuplicates(bool $condition = true): self
    {
        if ($condition) {
            $this->array = array_unique($this->array);
        }
        return $this;
    }

    /** 
     * Extracts a prop from each object of a given array and maps them in an array.
     * 
     * @param string $propGetterMethodOrPropName: the name of the getter method to use for property retrieval
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function extractPropsFromObjs(string $propGetterMethodOrPropName, bool $condition = true): self
    {
        if ($condition) {
            $this->array = array_map(
                function ($obj) use ($propGetterMethodOrPropName) {
                    if (is_object($obj)) {
                        return $this->getPropValue($obj, $propGetterMethodOrPropName);
                    }
                    return;
                },
                $this->array
            );
        }
        return $this;
    }


    /**
     * Map each entry of an indexed array to an associative array
     * where the keys are built by combining a user-defined prefix
     * and the index of the array entry (The index used is equal
     * to the position of the item in the array, not to the array index).
     * If the array has mixed keys, only the indexed keys will be changed
     * 
     * @param string $keyPrefix: the prefix used to build each array key
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function indexedToAssoc(string $keyPrefix = 'key', bool $condition = true): self
    {
        if ($condition) {
            $assocArray = array();
            $index = 0;
            foreach ($this->array as $key => $value) {
                if (is_int($key)) {
                    while (array_key_exists($keyPrefix . $index, $this->array)) {
                        $index++;
                    }
                    $assocArray[$keyPrefix . $index] = $value;
                } else {
                    $assocArray["$key"] = $value;
                }
                $index++;
            }
            $this->array = $assocArray;
        }
        return $this;
    }

    /**
     * @param  bool $condition
     */
    public function assocToIndexed(bool $condition = true): self
    {
        if ($condition) {
            $this->array = array_values($this->array);
        }
        return $this;
    }

    /**
     * Converts an indexed array of objects to an associative array  
     * using the value returned by the specified getter method as key.
     *
     * @param  string $propGetterMethodOrPropName: the name of the getter method to use for property retrieval
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function indexedToAssocWithObjProp(string $propGetterMethodOrPropName, bool $condition = true): self
    {
        if ($condition) {
            $assocArray = array();
            foreach ($this->array as $value) {
                $key = $this->getPropValue($value, $propGetterMethodOrPropName);
                $assocArray[$key] = $value;
            }

            $this->array = $assocArray;
        }
        return $this;
    }


    /**
     * Converts an indexed array in an associative array by using the entry value as key
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function indexedToAssocWithValue(bool $condition = true): self
    {
        if ($condition) {
            $assocArray = array();
            foreach ($this->array as $value) {
                if (!is_string($value) && !is_float($value) && !is_int($value)) {
                    throw new InvalidArgumentException(
                        'Cannot perform array conversion because one or more array entries are not of type string, int or float',
                        ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                    );
                }
                $assocArray[(string)$value] = $value;
            }

            $this->array = $assocArray;
        }
        return $this;
    }

    /**
     * Filters out the elements of an array whose keys does not contain the given substring
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterByKeySubstring(string $substring, bool $condition = true): self
    {
        if ($condition) {
            $matches = preg_grep('/' . $substring . '/', array_keys($this->array));
            $this->array = array_intersect_key($this->array, array_flip($matches));
        }
        return $this;
    }


    /**
     * @param  string $propGetterMethodOrPropName: the getter method to use for value retrieval from the objects stored in $array or the name of the prop
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function sortObjsByProp(string $propGetterMethodOrPropName, bool $desc = false, bool $condition = true): self
    {
        if ($condition) {
            if (count($this->array) === 0) {
                return $this;
            }

            //get the type of the prop on the first array entry
            $prop = $this->getPropValue(reset($this->array), $propGetterMethodOrPropName);
            $propType = gettype($prop);

            if ($propType === 'object') {
                foreach ($this->array as $element) {
                    if (
                        gettype($this->getPropValue($element, $propGetterMethodOrPropName)) !== 'object'
                        || $prop::class !== $this->getPropValue($element, $propGetterMethodOrPropName)::class
                    ) {
                        throw new InvalidArgumentException(
                            'Not all elements are of the same class or are objects',
                            ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                        );
                    }
                }
            } else {
                foreach ($this->array as $element) {
                    if ($propType !== gettype($this->getPropValue($element, $propGetterMethodOrPropName))) {
                        throw new InvalidArgumentException(
                            'Not all elements are of the same data type.',
                            ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                        );
                    }
                }
            }

            //we can only sort if the type of the prop is an integer, a float (double) or a string
            //for historical reasons "double" is returned in case of a float, and not simply "float"
            //check https://www.php.net/manual/en/function.gettype.php for details
            switch ($propType) {
                case 'float':
                case 'double':
                case 'integer':
                    usort($this->array, fn ($a, $b) => $this->getPropValue($a, $propGetterMethodOrPropName) <=> $this->getPropValue($b, $propGetterMethodOrPropName));
                    break;
                case 'string':
                    usort($this->array, fn ($a, $b) => strcmp($this->getPropValue($a, $propGetterMethodOrPropName), $this->getPropValue($b, $propGetterMethodOrPropName)));
                    break;
                case 'object':
                    if ($prop instanceof DateTime) {
                        usort($this->array, fn ($a, $b) => $this->getPropValue($a, $propGetterMethodOrPropName) <=> $this->getPropValue($b, $propGetterMethodOrPropName));
                        break;
                    }
                default:
                    throw new InvalidArgumentException(
                        "Cannot sort the array of object by prop because the provided prop has type $propType",
                        ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                    );
            }
            $this->array = $desc ? array_reverse($this->array) : $this->array;
        }
        return $this;
    }

    /**
     * @param  string $propGetterMethodsOrPropNames: the getter methods to use for value retrieval from the objects stored in $array or the name of the props
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function sortObjsByNestedProp(array $propGetterMethodsOrPropNames, bool $desc = false, bool $condition = true): self
    {
        if ($condition) {
            if (count($this->array) === 0) {
                return $this;
            }

            //get the type of the prop on the first array entry
            $prop = reset($this->array);
            foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                $prop = $this->getPropValueOrArrayEntry($prop, $propGetterMethodOrPropName);
            }
            $propType = gettype($prop);

            if ($propType === 'object') {
                foreach ($this->array as $element) {
                    $value = $element;
                    foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                        $value = $this->getPropValueOrArrayEntry($value, $propGetterMethodOrPropName);
                    }
                    if (
                        gettype($value) !== 'object'
                        || $prop::class !== $value::class
                    ) {
                        throw new InvalidArgumentException(
                            'Not all elements are of the same class or are objects',
                            ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                        );
                    }
                }
            } else {
                foreach ($this->array as $element) {
                    $value = $element;
                    foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                        $value = $this->getPropValueOrArrayEntry($value, $propGetterMethodOrPropName);
                    }
                    if ($propType !== gettype($value)) {
                        throw new InvalidArgumentException(
                            'Not all elements are of the same data type.',
                            ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                        );
                    }
                }
            }

            //we can only sort if the type of the prop is an integer, a float (double) or a string
            //for historical reasons "double" is returned in case of a float, and not simply "float"
            //check https://www.php.net/manual/en/function.gettype.php for details
            switch ($propType) {
                case 'float':
                case 'double':
                case 'integer':
                    usort($this->array, function ($a, $b) use ($propGetterMethodsOrPropNames): int {
                        foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                            $a = $this->getPropValueOrArrayEntry($a, $propGetterMethodOrPropName);
                            $b = $this->getPropValueOrArrayEntry($b, $propGetterMethodOrPropName);
                        }
                        return $a <=> $b;
                    });
                    break;
                case 'string':
                    usort($this->array, function ($a, $b) use ($propGetterMethodsOrPropNames): int {
                        foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                            $a = $this->getPropValueOrArrayEntry($a, $propGetterMethodOrPropName);
                            $b = $this->getPropValueOrArrayEntry($b, $propGetterMethodOrPropName);
                        }
                        return strcmp($a, $b);
                    });
                    break;
                case 'object':
                    if ($prop instanceof DateTime) {
                        usort($this->array, function ($a, $b) use ($propGetterMethodsOrPropNames): int {
                            foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                                $a = $this->getPropValueOrArrayEntry($a, $propGetterMethodOrPropName);
                                $b = $this->getPropValueOrArrayEntry($b, $propGetterMethodOrPropName);
                            }
                            return $a <=> $b;
                        });
                        break;
                    }
                default:
                    throw new InvalidArgumentException(
                        "Cannot sort the array of object by prop because the provided prop has type $propType",
                        ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                    );
            }
            $this->array = $desc ? array_reverse($this->array) : $this->array;
        }
        return $this;
    }

    /**
     * Shuffle the entries of the array
     */
    public function shuffle(bool $condition = true): self
    {
        if ($condition) {
            shuffle($this->array);
        }

        return $this;
    }

    /**
     * Filters the objects in an array by matching the value returned by the getter method against the specified value.
     * @param  mixed $value: the value to match (can be of type string, int, float and null)
     * @param  string $propGetterMethodOrPropName: the name of the getter method to call on the object or the name of the prop
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterObjsByProp($value, string $propGetterMethodOrPropName, bool $condition = true): self
    {
        if ($condition) {
            if (!is_string($value) && !is_int($value) && !is_float($value) && $value !== null && !is_bool($value)) {
                throw new InvalidArgumentException(
                    'The argument $value must be of type string, int, float or bool',
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                );
            }
            $this->array = array_filter(
                $this->array,
                fn ($obj): bool => $this->getPropValue($obj, $propGetterMethodOrPropName) === $value
            );

            $this->array = array_values($this->array);
        }
        return $this;
    }


    /**
     * @param  array $needles: array of valid values 
     * @param  string $propGetterMethodOrPropName: the getter method name to use to retrieve the prop value from each object of the haystack
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterObjsByProps(array $needles, string $propGetterMethodOrPropName, bool $condition = true): self
    {
        if ($condition) {
            $this->array = array_filter($this->array, fn ($obj) => in_array($this->getPropValue($obj, $propGetterMethodOrPropName), $needles));

            // Since PHP leaves the original index of their values, even on newer array after filtering
            // this step is necessary in order to have a better sorted array
            $this->array = array_values($this->array);
        }
        return $this;
    }


    /**
     * Filters the objects in the array by matching the value returned by the getter methods against the specified value.
     *
     * @param  array $propGetterMethodsOrPropNames: the names of the getter method to call on the object (the methods are called in the given order) or the prop names
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterObjsByNestedProp(string|int|float|bool|null $value, array $propGetterMethodsOrPropNames, bool $condition = true): self
    {
        if ($condition) {
            $this->array = array_filter(
                $this->array,
                function ($obj) use ($value, $propGetterMethodsOrPropNames): bool {
                    foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                        if (!isset($obj)) {
                            return false;
                        }
                        $obj = $this->getPropValue($obj, $propGetterMethodOrPropName);
                    }
                    $objValue = $obj;
                    return $value === $objValue;
                }
            );
        }
        return $this;
    }

    /**
     * Converts a two-dimensional array where the items are arrays which represents key/value pairs in an associative array
     * e.g.:
     * [
     *  ['key' => 'banana', value => 'yellow'],
     *  ['key' => 'strawberry', 'value' => 'red']
     * ]
     *  ... becomes:
     * ['banana' => 'yellow', 'strawberry' => 'red']
     * 
     * @param  bool $condition: no changes are applied when condition = false
     * @throws InvalidArgumentException if an error in encountered while converting the data.
     */
    public function keyValueArrayToAssoc(bool $condition = true): self
    {
        if ($condition) {
            $assoc = array();
            try {
                foreach ($this->array as $value) {
                    $key = $value['key'];
                    $val = $value['value'];
                    $assoc[$key] = $val;
                }
            } catch (Exception $exception) {
                throw new InvalidArgumentException(
                    'The conversion between a two-dimensional array of key-value arrays to associative array failed.',
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION,
                    $exception
                );
            }
            $this->array = $assoc;
        }
        return $this;
    }

    /**
     * Removes duplicate objects from the array by comparing the value returned by calling the specified getter method on each object.
     * If two or more objects with equal values are found, the last one wins!
     * 
     * @param  string $propGetterMethodOrPropName: the name of the getter method to call on the object or the name of the prop
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeDuplicateObjsByProp(string $propGetterMethodOrPropName, bool $condition = true): self
    {
        if ($condition) {
            $assocOfObjs = array();

            //Store each object in the array in an associative array.
            //As keys for the array we use the values returned by the
            //prop getter method.
            //If there are two or more objects in the array with the same prop value
            //only the last one will remain in the collection.
            foreach ($this->array as $obj) {
                $key = $this->getPropValue($obj, $propGetterMethodOrPropName);

                //the value returned by the getter method cannot be used to compare the objects!
                if (!is_string($key) && !is_int($key) && !is_float($key) && !$key instanceof DateTime) {
                    throw new InvalidArgumentException(
                        'Cannot use the prop value to filter duplicates in the array',
                        ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                    );
                }

                $key = $key instanceof DateTime ? $key->format(DateTime::ATOM) : $key;

                $assocOfObjs[$key] = $obj;
            }

            $this->array = array_values($assocOfObjs);
        }
        return $this;
    }

    /**
     * Remove objects from an array if the value returned by the specified getter method matches the specified value.
     * 
     * In other words: If $value is equal to the value returned by the $propGetterMethodOrPropName 
     * or from the value of the prop named $propGetterMethodOrPropName, the obj will be removed from the array.
     *
     * @param  string $propGetterMethodOrPropName: the name of the getter method to call on the object or the name of the prop
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeObjsByProp(string|int|float|bool|null $value, string $propGetterMethodOrPropName, bool $condition = true): self
    {
        if ($condition) {
            if (!is_string($value) && !is_int($value) && !is_float($value) && $value !== null) {
                throw new InvalidArgumentException(
                    'The argument $value must be of type string, int, float, or must be null',
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                );
            }
            $this->array = array_filter(
                $this->array,
                fn ($obj): bool => $this->getPropValue($obj, $propGetterMethodOrPropName) !== $value
            );
        }
        return $this;
    }


    /**
     * Remove duplicates from an array of objects implementing EquatableDocumentInterface
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeEqualObjs(bool $condition = true): ArrayManipulator
    {
        if ($condition) {
            $filteredObjsSet = $this->array;

            if (!ArrayValidator::hasOnlyInstancesOfClass($filteredObjsSet, EquatableDocumentInterface::class)) {
                throw new InvalidArgumentException(
                    sprintf('Not all objects in the array are instances of %s', EquatableDocumentInterface::class,),
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION,
                );
            }

            /** @var EquatableDocumentInterface $obj */
            foreach ($filteredObjsSet as $key => $obj) {
                $objsSetWithoutCurrentObj = $filteredObjsSet;
                unset($objsSetWithoutCurrentObj[$key]);

                /** @var EquatableDocumentInterface $objToCompare */
                foreach ($objsSetWithoutCurrentObj as $objToCompare) {
                    if ($obj->isEqualTo($objToCompare)) {
                        unset($filteredObjsSet[$key]);
                    }
                }
            }

            $this->array = array_values($filteredObjsSet);
        }
        return $this;
    }


    /**
     * Remove duplicates from an array of objects implementing App\Contract\Document\IdenticalDocumentInterface
     * 
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeIdenticalObjs(bool $condition = true): ArrayManipulator
    {
        if ($condition) {
            $filteredObjsSet = $this->array;

            if (!ArrayValidator::hasOnlyInstancesOfClass($filteredObjsSet, IdenticalDocumentInterface::class)) {
                throw new InvalidArgumentException(
                    sprintf('Not all objects in the array are instances of %s', IdenticalDocumentInterface::class,),
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION,
                );
            }

            /** @var IdenticalDocumentInterface $obj */
            foreach ($filteredObjsSet as $key => $obj) {
                $objsSetWithoutCurrentObj = $filteredObjsSet;
                unset($objsSetWithoutCurrentObj[$key]);

                /** @var IdenticalDocumentInterface $objToCompare */
                foreach ($objsSetWithoutCurrentObj as $objToCompare) {
                    if ($obj->isIdenticalTo($objToCompare)) {
                        unset($filteredObjsSet[$key]);
                    }
                }
            }

            $this->array = array_values($filteredObjsSet);
        }
        return $this;
    }

    /**
     * Filters the objects in an array by matching the value returned by the getter methods/properties against the specified value.
     * Both arrays must have the same size and for checking the schema to follow is:
     * values: [1, 2, 3] names: [a, b, c] 
     * checks: 1 === $obj->$a(); 2 === $obj->$b(); etc.
     * 
     * @param  array $values: the list of values to match
     * @param  array $propGetterMethodOrPropNames: the names of the getter method to call on the object or the name of the prop
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterObjsByMultipleProps(array $values, array $propGetterMethodOrPropNames, bool $condition = true): self
    {
        if ($condition) {
            if (count($values) !== count($propGetterMethodOrPropNames)) {
                throw new InvalidArgumentException(
                    'Array of values and array of props/methods must have the same length',
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                );
            }

            $this->array = array_filter(
                $this->array,
                function ($obj) use ($values, $propGetterMethodOrPropNames): bool {
                    $valuesCount = count($values);
                    for ($i = 0; $i < $valuesCount; $i++) {
                        if ($values[$i] !== $this->getPropValue($obj, $propGetterMethodOrPropNames[$i])) {
                            return false;
                        }
                    }
                    return true;
                }
            );
        }
        return $this;
    }

    /**
     * Groups the objects of the array in sub-arrays.
     *
     * @param  string $propGetterMethodOrPropName: the name of the getter method to call on each object
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function groupObjsByProp(string $propGetterMethodOrPropName, bool $condition = true): self
    {
        if ($condition) {
            $groups = array();
            foreach ($this->array as $obj) {
                $propValue = $this->getPropValueOrArrayEntry($obj, $propGetterMethodOrPropName);

                if (!is_scalar($propValue)) {
                    throw new InvalidArgumentException(
                        'Cannot group objects: the type of the prop must be a scalar.',
                        ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                    );
                }

                $groupKey = strval($propValue);
                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = array();
                }

                $groups[$groupKey][] = $obj;
            }
            $this->array = array_values($groups);
        }
        return $this;
    }

    /**
     * Groups the objects of the array in sub-arrays.
     *
     * @param  array $propGetterMethodsOrPropNames: the name of the getter methods to call on each object
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function groupObjsByNestedProp(array $propGetterMethodsOrPropNames, bool $condition = true): self
    {
        if ($condition) {
            $groups = array();
            foreach ($this->array as $obj) {

                $propValue = $obj;
                foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                    $propValue = $this->getPropValueOrArrayEntry($propValue, $propGetterMethodOrPropName);
                }

                if (!is_scalar($propValue)) {
                    throw new InvalidArgumentException(
                        'Cannot group objects: the type of the prop must be a scalar.',
                        ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                    );
                }

                $groupKey = strval($propValue);
                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = array();
                }

                $groups[$groupKey][] = $obj;
            }
            $this->array = array_values($groups);
        }
        return $this;
    }

    /**
     * Groups the objects of the array in sub-arrays by a value of a sub-array.
     *
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function groupBySubarrayValue(int|string $subarrayKey, bool $condition = true): self
    {
        if ($condition) {
            $groups = array();
            foreach ($this->array as $key => $value) {

                switch (gettype($value[$subarrayKey])) {
                    case "string":
                    case "integer":
                    case "double":
                    case "float":
                        $groups[$value[$subarrayKey]][$key] = $value;
                        break;
                    case "boolean":
                        $index = $value[$subarrayKey] ? 'true' : 'false';
                        $groups[$index][$key] = $value;
                        break;
                    default:
                        throw new InvalidArgumentException(
                            "array items should not contain any object/not-castable-to-string values",
                            ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                        );
                }
            }
            $this->array = $groups;
        }
        return $this;
    }

    /**
     * Extract a slice of the array using php array_slice function
     * @see https://www.php.net/manual/en/function.array-slice.php
     *
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function slice(int $offset, ?int $limit, bool $condition = true): self
    {
        if ($condition) {
            $this->array = array_slice($this->array, $offset, $limit);
        }
        return $this;
    }


    /**
     * Flatten a two-dimensional array
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function flatten(bool $condition = true): self
    {
        if ($condition) {
            $flatArray = array();
            foreach ($this->array as $value) {
                if (is_array($value)) {
                    $flatArray = array_merge($flatArray, $value);
                } else {
                    $flatArray[] = $value;
                }
            }
            $this->array = $flatArray;
        }
        return $this;
    }


    /**
     * Converts an array of Collections in a multi-dimensional array.
     * E.g.: 
     * from: [ new ArrayCollection([...]), new ArrayCollection(...)] to [[...], [...]]
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function collectionsToArrays(bool $condition = true): self
    {
        if ($condition) {
            $this->array = array_map(fn (Collection $collection) => $collection->toArray(), $this->array);
        }
        return $this;
    }

    public function get(): array
    {
        return $this->array;
    }

    /**
     * Get the values as a collection
     */
    public function getAsCollection(): Collection
    {
        return new ArrayCollection($this->array);
    }

    /**
     * Set the value of the $count variable to the current array length
     */
    public function setCount(int &$count): self
    {
        $count = count($this->array);

        return $this;
    }

    /**
     * Returns the first result in the array or null if the array is empty
     */
    public function getFirstOrNull(): mixed
    {
        return count($this->array) > 0
            ? reset($this->array)
            : null;
    }

    /**
     * Returns the first result of the array or the passed default value if the array is empty
     * @param  mixed $default: the default return value
     */
    public function getFirstOrDefault(mixed $default): mixed
    {
        return count($this->array) > 0
            ? reset($this->array)
            : $default;
    }


    /**
     * Resolve the prop value by calling the getter method.
     * Fallback to reflection Api if the given object has no method called $propGetterMethodOrPropName.
     *
     * @throws InvalidArgumentException if the prop value cannot be retrieved because $propGetterMethodOrPropName
     * is neither a valid getter method nor a valid prop name.
     */
    private function getPropValue(object $obj, string $propGetterMethodOrPropName)
    {
        try {
            return $obj->$propGetterMethodOrPropName();
        } catch (Error) {
            try {
                return ReflectionHelper::getPrivateProp($obj, $propGetterMethodOrPropName);
            } catch (ReflectionException $exception) {
                throw new InvalidArgumentException(
                    sprintf('Invalid prop getter method or prop name: %s', $propGetterMethodOrPropName),
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION,
                    $exception
                );
            }
        }
    }

    /**
     * Resolve the prop value, or extract the array entry by key
     */
    private function getPropValueOrArrayEntry(object|array $obj, string $propGetterMethodOrPropNameOrArrayKey)
    {
        if (is_array($obj)) {
            return $obj[$propGetterMethodOrPropNameOrArrayKey];
        }
        return $this->getPropValue($obj, $propGetterMethodOrPropNameOrArrayKey);
    }
}
