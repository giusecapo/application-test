<?php

declare(strict_types=1);

namespace App\Utility;

use App\Contract\Document\EquatableDocumentInterface;
use App\Contract\Document\IdenticalDocumentInterface;
use Doctrine\Common\Collections\Collection;
use \InvalidArgumentException as InvalidArgumentException;
use function count;
use function is_bool;
use function is_int;
use function is_float;
use function is_string;
use function is_array;
use function is_object;
use function is_callable;

final class CollectionValidator
{

    /**
     * hasObjArrCallAsChildren: check if a collection contains objects, arrays or functions
     * @param Collection $collection: the collection to validate
     * @return bool 
     */
    public static function hasObjArrCallAsChildren(Collection $collection): bool
    {
        $array = $collection->toArray();
        foreach ($array as $value) {
            if (is_object($value) || is_array($value) || is_callable($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * hasOnlyScalarsAsChildren
     *
     * @param  Collection $collection
     *
     * @return bool
     */
    public static function hasOnlyScalarsAsChildren(Collection $collection): bool
    {
        $array = $collection->toArray();
        foreach ($array as $value) {
            if (!is_scalar($value)) {
                return false;
            }
        }
        return true;
    }


    /**
     * hasOnlyObjs: check if a collection contains only objects
     * 
     * @param Collection $collection
     * 
     * @return bool
     */
    public static function hasOnlyObjs(Collection $collection): bool
    {
        $array = $collection->toArray();
        foreach ($array as $value) {
            if (!is_object($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * hasOnlyStrings: check if an collection has only string values
     * 
     * @param Collection $collection
     * 
     * @return bool: returns false if the collection is empty
     */
    public static function hasOnlyStrings(Collection $collection): bool
    {
        $array = $collection->toArray();
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

    /**
     * hasOnlyNumbers: check if a collection has only numeric values (performs typechecking)
     * @param Collection $collection: the collection to check
     * @return bool: returns false if the collection is empty
     */
    public static function hasOnlyNumbers(Collection $collection): bool
    {
        $array = $collection->toArray();
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

    /**
     * hasOnlyNumbersNoTypeCheck: check if a collection has only numeric values (without typechecking)
     * @param Collection $collection: the collection to check
     * @return bool: returns false if the collection is empty
     */
    public static function hasOnlyNumbersNoTypeCheck(Collection $collection): bool
    {
        $array = $collection->toArray();
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

    /**
     * hasOnlyPositiveInt: check if a collection has only positive integers as values (with typechecking)
     * @param Collection $collection: the collection to check
     * @return bool: returns false if the collection is empty
     */
    public static function hasOnlyPositiveInt(Collection $collection): bool
    {
        $array = $collection->toArray();
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

    /**
     * hasOnlyPositiveIntNoTypeCheck: check if a collection has only positive integers as values (without typechecking)
     * @param Collection $collection: the collection to check
     * @return bool: returns false if the collection is empty
     */
    public static function hasOnlyPositiveIntNoTypeCheck(Collection $collection): bool
    {
        $array = $collection->toArray();
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

    /**
     * hasOnlyInt: check if a collection has only integers as values (with typechecking)
     * @param Collection $collection: the collection to check
     * @return bool: returns false if the collection is empty
     */
    public static function hasOnlyInt(Collection $collection): bool
    {
        $array = $collection->toArray();
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

    /**
     * hasOnlyIntNoTypeCheck: check if a collection has only integers as values
     * @param Collection $collection: the collection to check
     * @return bool: returns false if the collection is empty
     */
    public static function hasOnlyIntNoTypeCheck(Collection $collection): bool
    {
        $array = $collection->toArray();
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


    /**
     * hasOnlyInstancesOfClass: check if a collection contains only instances of a specific class
     * @param Collection $collection: the array to validate
     * @param string $className
     * @return bool
     */
    public static function hasOnlyInstancesOfClass(Collection $collection, string $className): bool
    {
        $array = $collection->toArray();
        foreach ($array as $value) {
            if (!$value instanceof $className) {
                return false;
            }
        }
        return true;
    }


    /**
     * hasOnlyInstancesOfClasses: check if a collection contains only instances of the specified classes
     * This method returns true if any object in the collection is an instance of at least one of the given classes.
     * @param string[] $classNames
     */
    public static function hasOnlyInstancesOfClasses(Collection $collection, array $classNames): bool
    {
        $array = $collection->toArray();
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

    /**
     * hasOnlyBoolTrue: check if an array contains only (bool)true values.
     * @param Collection $collection: the collection to validate
     * @return bool
     */
    public static function hasOnlyBoolTrue(Collection $collection): bool
    {
        $array = $collection->toArray();
        foreach ($array as $value) {
            if (!is_bool($value) || !$value) {
                return false;
            }
        }
        return true;
    }

    /**
     * validateElements: validate each element in a collection against a custom callback public static function
     * @param Collection $collection: the collection to validate
     * @param callable $callback: the validation public static function
     * @return bool true if the callback public static function always returns true, false otherwise
     */

    public static function validateElements(Collection $collection, callable $callback): bool
    {
        $array = $collection->toArray();
        foreach ($array as $value) {
            if ($callback($value) !== true) {
                return false;
            }
        }
        return true;
    }

    /**
     * isEqualTo: check if two collections contain the same data set
     * This method is not suited to compare collections of objects.
     *
     * @param  Collection $collection1
     * @param  Collection $collection2
     *
     * @return bool
     */
    public static function isEqualTo(Collection $collection1, Collection $collection2): bool
    {
        $array1 = $collection1->toArray();
        $array2 = $collection2->toArray();
        return count($array1) == count($array2)
            && array_diff($array1, $array2) === array_diff($array2, $array1);
    }

    /**
     * haveEqualObjects: check if two collections contain equal objects
     * 
     * All objects in the two collections must implement the App\Contract\Document\EquatableDocumentInterface
     *
     * @param  Collection $collection1 
     * @param  Collection $collection2
     *
     * @return bool
     * @throws InvalidArgumentException if one or more objects in the collections do no implement App\Contract\Document\EquatableDocumentInterface
     */
    public static function haveEqualObjects(Collection $collection1, Collection $collection2): bool
    {
        if (
            !self::hasOnlyInstancesOfClass($collection1, EquatableDocumentInterface::class)
            || !self::hasOnlyInstancesOfClass($collection2, EquatableDocumentInterface::class)
        ) {
            throw new InvalidArgumentException(sprintf('One or more objects in the collection do not implement the %s', EquatableDocumentInterface::class));
        }


        $c1 = (new CollectionManipulator($collection1))->removeEqualObjs()->get();
        $c2 = (new CollectionManipulator($collection2))->removeEqualObjs()->get();

        if ($c1->count() !== $c2->count()) {
            return false;
        }

        /** @var EquatableDocumentInterface $obj1 */
        foreach ($c1 as $obj1) {
            $collectionManipulator = new CollectionManipulator($c2);
            $numberOfDuplicates = $collectionManipulator
                ->filter(fn (EquatableDocumentInterface $obj2): bool => $obj2->isEqualTo($obj1))
                ->get()
                ->count();
            if ($numberOfDuplicates != 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * haveIdenticalObjects: check if two collections contain identical objects
     * 
     * All objects in the two collections must implement the App\Contract\Document\IdenticalDocumentInterface
     *
     * @param  Collection $collection1 
     * @param  Collection $collection2
     *
     * @return bool
     * @throws InvalidArgumentException if one or more objects in the collections do no implement App\Contract\Document\IdenticalDocumentInterface
     */
    public static function haveIdenticalObjects(Collection $collection1, Collection $collection2): bool
    {
        if (
            !self::hasOnlyInstancesOfClass($collection1, IdenticalDocumentInterface::class)
            || !self::hasOnlyInstancesOfClass($collection2, IdenticalDocumentInterface::class)
        ) {
            throw new InvalidArgumentException(sprintf('One or more objects in the collection do not implement the %s', IdenticalDocumentInterface::class));
        }


        $c1 = (new CollectionManipulator($collection1))->removeIdenticalObjs()->get();
        $c2 = (new CollectionManipulator($collection2))->removeIdenticalObjs()->get();

        if ($c1->count() !== $c2->count()) {
            return false;
        }

        /** @var IdenticalDocumentInterface $obj1 */
        foreach ($c1 as $obj1) {
            $collectionManipulator = new CollectionManipulator($c2);
            $numberOfDuplicates = $collectionManipulator
                ->filter(fn (IdenticalDocumentInterface $obj2): bool => $obj2->isIdenticalTo($obj1))
                ->get()
                ->count();
            if ($numberOfDuplicates != 1) {
                return false;
            }
        }

        return true;
    }
}
