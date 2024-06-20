<?php

declare(strict_types=1);

namespace App\Utility;

use App\Contract\Document\EquatableDocumentInterface;
use App\Contract\Document\IdenticalDocumentInterface;
use App\Service\Constant\ExceptionCodes;
use Closure;
use Doctrine\Common\Collections\Collection;
use \InvalidArgumentException as InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;
use \DateTime as DateTime;
use \Error as Error;
use \ReflectionException as ReflectionException;

use function count;
use function in_array;
use function is_bool;
use function is_int;
use function is_float;
use function is_string;
use function is_object;
use function gettype;

final class CollectionManipulator
{

    public function __construct(private Collection $collection)
    {
    }

    /**
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function sortObjsByProp(string $propGetterMethodOrPropName, bool $desc = false, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            //nothing to do here...
            if ($this->collection->isEmpty()) {
                return $this;
            }

            //convert the collection to an array
            $arrayOfObjs = $this->collection->toArray();

            //get the type of the prop on the first array entry
            $prop = $this->getPropValue(reset($arrayOfObjs), $propGetterMethodOrPropName);
            $propType = gettype($prop);

            if ($propType === 'object') {
                foreach ($arrayOfObjs as $element) {
                    if (
                        getType($this->getPropValue($element, $propGetterMethodOrPropName)) !== 'object'
                        || $prop::class !== $this->getPropValue($element, $propGetterMethodOrPropName)::class
                    ) {
                        throw new InvalidArgumentException(
                            'Not all elements are of the same class or are objects',
                            ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                        );
                    }
                }
            } else {
                foreach ($arrayOfObjs as $element) {
                    if ($propType !== getType($this->getPropValue($element, $propGetterMethodOrPropName))) {
                        throw new InvalidArgumentException(
                            'Not all elements are of the same data type.',
                            ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                        );
                    }
                }
            }

            //We can only sort if the type of the prop is an integer, a float (double) or a string.
            //For historical reasons "double" is returned in case of a float, and not simply "float"
            //check https://www.php.net/manual/en/function.gettype.php for details
            switch ($propType) {
                case "float":
                case "double":
                case "integer":
                    usort(
                        $arrayOfObjs,
                        fn ($a, $b) => $this->getPropValue($a, $propGetterMethodOrPropName) <=> $this->getPropValue($b, $propGetterMethodOrPropName)
                    );
                    break;
                case "string":
                    usort(
                        $arrayOfObjs,
                        fn ($a, $b) => strcmp($this->getPropValue($a, $propGetterMethodOrPropName), $this->getPropValue($b, $propGetterMethodOrPropName))
                    );
                    break;
                case 'object':
                    if ($prop instanceof DateTime) {
                        usort(
                            $arrayOfObjs,
                            fn ($a, $b) => $this->getPropValue($a, $propGetterMethodOrPropName)  <=> $this->getPropValue($b, $propGetterMethodOrPropName)
                        );
                        break;
                    }
                default:
                    throw new InvalidArgumentException(
                        "Cannot sort the collection of object by prop because the provided prop is of type $propType",
                        ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                    );
            }
            //if desc is true, reverse the array
            $arrayOfObjs = $desc ? array_reverse($arrayOfObjs) : $arrayOfObjs;

            //rebuild the collection

            //We cannot call collection->clear() here because this 
            //would lead to problems with doctrine persistent collections
            //when working with documents fetched with read-only queries
            foreach ($arrayOfObjs as $key => $obj) {
                $itemKey = $this->collection->indexOf($obj);
                if ($itemKey !== false) {
                    $this->collection->remove($itemKey);
                }
            }

            foreach ($arrayOfObjs as $key => $obj) {
                $this->collection->set($key, $obj);
            }
        }
        return $this;
    }


    /**
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function sortObjsByNestedProp(array $propGetterMethodsOrPropNames, bool $desc = false, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            //nothing to do here...
            if ($this->collection->isEmpty()) {
                return $this;
            }

            //convert the collection to an array
            $arrayOfObjs = $this->collection->toArray();

            //get the type of the prop on the first array entry
            $prop = reset($arrayOfObjs);
            foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                $prop = $this->getPropValue($prop, $propGetterMethodOrPropName);
            }
            $propType = gettype($prop);

            if ($propType === 'object') {
                foreach ($arrayOfObjs as $element) {
                    $value = $element;
                    foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                        $value = $this->getPropValue($value, $propGetterMethodOrPropName);
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
                foreach ($arrayOfObjs as $element) {
                    $value = $element;
                    foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                        $value = $this->getPropValue($value, $propGetterMethodOrPropName);
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
                    usort($arrayOfObjs, function ($a, $b) use ($propGetterMethodsOrPropNames): int {
                        foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                            $a = $this->getPropValue($a, $propGetterMethodOrPropName);
                            $b = $this->getPropValue($b, $propGetterMethodOrPropName);
                        }
                        return $a  <=> $b;
                    });
                    break;
                case 'string':
                    usort($arrayOfObjs, function ($a, $b) use ($propGetterMethodsOrPropNames): int {
                        foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                            $a = $this->getPropValue($a, $propGetterMethodOrPropName);
                            $b = $this->getPropValue($b, $propGetterMethodOrPropName);
                        }
                        return strcmp($a, $b);
                    });
                    break;
                case 'object':
                    if ($prop instanceof DateTime) {
                        usort($arrayOfObjs, function ($a, $b) use ($propGetterMethodsOrPropNames): int {
                            foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                                $a = $this->getPropValue($a, $propGetterMethodOrPropName);
                                $b = $this->getPropValue($b, $propGetterMethodOrPropName);
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
            //if desc is true, reverse the array
            $arrayOfObjs = $desc ? array_reverse($arrayOfObjs) : $arrayOfObjs;

            //rebuild the collection

            //We cannot call collection->clear() here because this 
            //would lead to problems with doctrine persistent collections
            //when working with documents fetched with read-only queries
            foreach ($arrayOfObjs as $key => $obj) {
                $itemKey = $this->collection->indexOf($obj);
                if ($itemKey !== false) {
                    $this->collection->remove($itemKey);
                }
            }

            foreach ($arrayOfObjs as $key => $obj) {
                $this->collection->set($key, $obj);
            }
        }
        return $this;
    }

    /**
     * Shuffle the entries of the array
     */
    public function shuffle(bool $condition = true): self
    {
        if ($condition) {
            $array = $this->collection->toArray();
            shuffle($array);

            //We cannot call collection->clear() here because this 
            //would lead to problems with doctrine persistent collections
            //when working with documents fetched with read-only queries
            foreach ($array as $key => $obj) {
                $itemKey = $this->collection->indexOf($obj);
                if ($itemKey !== false) {
                    $this->collection->remove($itemKey);
                }
            }

            foreach ($array as $key => $obj) {
                $this->collection->set($key, $obj);
            }
        }

        return $this;
    }

    /**
     * Removes duplicate objects from the collection by comparing the value returned by calling the specified getter method.
     * If two or more objects with equal values are found, the last one wins!
     *
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeDuplicateObjsByProp(string $propGetterMethodOrPropName, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $arrayOfObjs = $this->collection->toArray();
            $assocOfObjs = array();

            //Store each object in the collection in an associative array.
            //As keys for the array we use the values returned by the
            //prop getter method.
            //If there are two or more objects in the collection with the same prop value
            //only the last one will remain in the collection.
            foreach ($arrayOfObjs as $obj) {
                $key = $this->getPropValue($obj, $propGetterMethodOrPropName);

                //the value returned by the getter method cannot be used to compare the objects!
                if (!is_string($key) && !is_int($key) && !is_float($key) && !$key instanceof DateTime) {
                    throw new InvalidArgumentException(
                        "Cannot use the value of the specified prop to filter out duplicates in the collection",
                        ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                    );
                }

                $key = $key instanceof DateTime ? $key->format(DateTime::ATOM) : $key;

                $assocOfObjs[$key] = $obj;
            }

            //Clear the collection and insert the filtered objects.
            //We cannot call collection->clear() here because this 
            //would lead to problems with doctrine persistent collections
            //when working with documents fetched with read-only queries
            foreach ($arrayOfObjs as $obj) {
                $this->collection->removeElement($obj);
            }

            $i = 0;
            foreach ($assocOfObjs as $obj) {
                $this->collection->set($i, $obj);
                $i++;
            }
        }
        return $this;
    }


    /**
     * Remove duplicate values in a collection of scalar types (int, float, string, bool, null)
     * 
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeDuplicates(bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $array = $this->collection->toArray();
            $array = array_unique($array);
            $this->collection = new ArrayCollection($array);
        }
        return $this;
    }


    /**
     * Removes duplicate objects from the collection by comparing the value returned by calling the specified getter methods. 
     * If two or more objects with equal values are found, the last one wins!
     *
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeDuplicateObjsByProps(array $propGetterMethodsOrPropNames, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $arrayOfObjs = $this->collection->toArray();
            $assocOfObjs = array();

            //Store each object in the collection in an associative array.
            //As keys for the array we use the values returned by the
            //prop getter method.
            //If there are two or more objects in the collection with the same prop value
            //only the last one will remain in the collection.
            foreach ($arrayOfObjs as $obj) {
                $key = '';
                foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                    $propValue = $this->getPropValue($obj, $propGetterMethodOrPropName);
                    //the value returned by the getter method cannot be used to compare the objects!
                    if (!is_string($propValue) && !is_int($propValue) && !is_float($propValue)) {
                        throw new InvalidArgumentException(
                            "Cannot use the value of the specified prop to filter out duplicates in the collection",
                            ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                        );
                    }
                    $key = sprintf('%s@%s', $key, $propValue);
                }

                $assocOfObjs[$key] = $obj;
            }

            //Clear the collection and insert the filtered objects.
            //We cannot call collection->clear() here because this 
            //would lead to problems with doctrine persistent collections
            //when working with documents fetched with read-only queries
            foreach ($arrayOfObjs as $obj) {
                $this->collection->removeElement($obj);
            }

            $i = 0;
            foreach ($assocOfObjs as $obj) {
                $this->collection->set($i, $obj);
                $i++;
            }
        }
        return $this;
    }

    /**
     * Remove duplicates from a collection of objects implementing EquatableDocumentInterface
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeEqualObjs(bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $filteredObjsSet = $this->collection->toArray();

            if (!ArrayValidator::hasOnlyInstancesOfClass($filteredObjsSet, EquatableDocumentInterface::class)) {
                throw new InvalidArgumentException(
                    sprintf('Not all objects in the collection are instances of %s', EquatableDocumentInterface::class,),
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

            $this->collection = new ArrayCollection(array_values($filteredObjsSet));
        }
        return $this;
    }


    /**
     * Remove duplicates from a collection of objects implementing App\Contract\Document\IdenticalDocumentInterface
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeIdenticalObjs(bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $filteredObjsSet = $this->collection->toArray();

            if (!ArrayValidator::hasOnlyInstancesOfClass($filteredObjsSet, IdenticalDocumentInterface::class)) {
                throw new InvalidArgumentException(
                    sprintf('Not all objects in the collection are instances of %s', IdenticalDocumentInterface::class,),
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

            $this->collection = new ArrayCollection(array_values($filteredObjsSet));
        }
        return $this;
    }

    /**
     * Remove objects from a collection if the value returned by the specified getter method matches the specified value.
     * In other words: If $value is equal to the value returned by the $propGetterMethodOrPropName, the obj will be removed from the collection.
     *
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeObjsByProp(string|int|float|bool|null $value, string $propGetterMethodOrPropName, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value) && $value !== null) {
                throw new InvalidArgumentException(
                    'The argument $value must be of type string, int, float or must be null',
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                );
            }
            $this->collection = $this->collection->filter(
                fn ($obj): bool => $this->getPropValue($obj, $propGetterMethodOrPropName) !== $value
            );
        }
        return $this;
    }


    /**
     * Remove objects from a collection if the value returned by the specified getter method matches one of the specified values.
     * 
     * In other words: If $values contains the value returned by the $propGetterMethodOrPropName, the obj will be removed from the collection.
     *
     * @param  array $needles: the values to match 
     * @param  string $propGetterMethodOrPropName: the name of the getter method to call on the object or the name of the prop
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function removeObjsByProps(array $needles, string $propGetterMethodOrPropName, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $this->collection = $this->collection->filter(
                fn ($obj): bool => !in_array($this->getPropValue($obj, $propGetterMethodOrPropName), $needles, true)
            );
        }
        return $this;
    }


    /**
     * Filters the objects in a collection by matching the value returned by the getter method against the specified value.
     *
     * @param  string $propGetterMethodOrPropName: the name of the getter method to call on the object or the name of the prop
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterObjsByProp(string|int|float|bool|null $value, string $propGetterMethodOrPropName, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value) && $value !== null) {
                throw new InvalidArgumentException(
                    'The argument $value must be of type string, int, float or bool.',
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                );
            }
            $this->collection = $this->collection->filter(
                fn ($obj): bool => $this->getPropValue($obj, $propGetterMethodOrPropName) === $value
            );
        }
        return $this;
    }

    /**
     * Filters the objects in a collection by matching the value returned by the getter method against the specified values.
     * 
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterObjsByProps(array $needles, string $propGetterMethodOrPropName, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $this->collection = $this->collection->filter(
                fn ($obj): bool => in_array($this->getPropValue($obj, $propGetterMethodOrPropName), $needles, true)
            );
        }
        return $this;
    }


    /**
     * Filters the objects in a collection by matching the value returned by the getter methods against the specified value.
     *
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterObjsByNestedProp(string|int|float|bool|null $value, array $propGetterMethodsOrPropNames, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value) && $value !== null) {
                throw new InvalidArgumentException(
                    'The argument $value must be of type string, int, float or bool',
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                );
            }
            $this->collection = $this->collection->filter(
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
     * Filters the objects in a collection by matching the value returned by the getter method against the specified values.
     * 
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterObjsByNestedProps(array $needles, array $propGetterMethodsOrPropNames, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $this->collection = $this->collection->filter(
                function ($obj) use ($needles, $propGetterMethodsOrPropNames): bool {
                    foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                        if (!isset($obj)) {
                            return false;
                        }
                        $obj = $this->getPropValue($obj, $propGetterMethodOrPropName);
                    }
                    $objValues = $obj;
                    return in_array($objValues, $needles, true);
                }
            );
        }
        return $this;
    }

    /**
     * Filters the objects in a collection by matching the values returned by the getter methods/properties against the specified values.
     * 
     * Both arrays must have the same size and for checking the schema to follow is:
     * values: [1, 2, 3] names: [a, b, c] 
     * checks: 1 === $obj->$a(); 2 === $obj->$b(); etc.
     *
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filterObjsByMultipleProps(array $values, array $propGetterMethodOrPropNames, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            if (count($values) !== count($propGetterMethodOrPropNames)) {
                throw new InvalidArgumentException(
                    'Array of values and array of props/methods must have the same length',
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                );
            }

            $this->collection = $this->collection->filter(
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
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function filter(Closure $closure, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $this->collection = $this->collection->filter($closure);
        }
        return $this;
    }


    /**
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function map(Closure $closure, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $this->collection = $this->collection->map($closure);
        }
        return $this;
    }

    /** 
     * Extracts a prop from each object of the given collection and maps them in a new collection.
     * 
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function extractPropsFromObjs(string $propGetterMethodOrPropName, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $array = $this->collection->toArray();
            $values = array_map(
                function ($obj) use ($propGetterMethodOrPropName) {
                    if (is_object($obj)) {
                        return $this->getPropValue($obj, $propGetterMethodOrPropName);
                    }
                    return;
                },
                $array
            );

            $this->collection = new ArrayCollection($values);
        }
        return $this;
    }

    /** 
     * Extracts a prop nested inside the objects of the collection and maps them in a new collection.
     * 
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function extractNestedPropsFromObjs(array $propGetterMethodsOrPropNames, bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $array = $this->collection->toArray();
            $values = array_map(
                function ($obj) use ($propGetterMethodsOrPropNames) {
                    foreach ($propGetterMethodsOrPropNames as $propGetterMethodOrPropName) {
                        if (!is_object($obj)) {
                            return;
                        }
                        $obj = $this->getPropValue($obj, $propGetterMethodOrPropName);
                    }
                    return $obj;
                },
                $array
            );

            $this->collection = new ArrayCollection($values);
        }
        return $this;
    }

    /**
     * Extract a slice of the collection
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function slice(int $offset, ?int $limit, bool $condition = true): self
    {
        if ($condition) {
            $this->collection = new ArrayCollection($this->collection->slice($offset, $limit));
        }
        return $this;
    }

    /**
     * Flatten a two-dimensional collection
     * @param  bool $condition: no changes are applied when condition = false
     */
    public function flatten(bool $condition = true): CollectionManipulator
    {
        if ($condition) {
            $flatArray = array();
            foreach ($this->collection->toArray() as $value) {
                if ($value instanceof Collection) {
                    $flatArray = array_merge($flatArray, $value->toArray());
                } else {
                    $flatArray[] = $value;
                }
            }
            $this->collection = new ArrayCollection($flatArray);
        }
        return $this;
    }


    public function get(): Collection
    {
        return $this->collection;
    }

    /**
     * Returns the collection values as array
     */
    public function getAsArray(): array
    {
        return $this->collection->toArray();
    }

    /**
     * Set the value of the $count variable to the current collection length
     */
    public function setCount(int &$count): self
    {
        $count = $this->collection->count();

        return $this;
    }

    /**
     * Returns the first result of the collection, or null if the collection is empty
     */
    public function getFirstOrNull(): mixed
    {
        return !$this->collection->isEmpty()
            ? $this->collection->first()
            : null;
    }


    /**
     * Returns the first result of the collection, or the passed default value if the collection is empty
     */
    public function getFirstOrDefault(mixed $default): mixed
    {
        return !$this->collection->isEmpty()
            ? $this->collection->first()
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
}
