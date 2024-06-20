<?php

declare(strict_types=1);

namespace App\Utility;

use ReflectionObject;
use \ReflectionClass as ReflectionClass;

final class ReflectionHelper
{

    public static function getPrivateProp(object $obj, string $propName)
    {
        $reflectionObject = new ReflectionObject($obj);
        $propProperty = $reflectionObject->getProperty($propName);
        $propProperty->setAccessible(true);
        return $propProperty->getValue($obj);
    }

    public static function setPrivateProp(object $obj, string $propName, $value): void
    {
        $reflectionObject = new ReflectionObject($obj);
        $propProperty = $reflectionObject->getProperty($propName);
        $propProperty->setAccessible(true);
        $propProperty->setValue($obj, $value);
    }

    public static function getClassConstants(string $className): array
    {
        $reflectionClass = new ReflectionClass($className);
        return $reflectionClass->getConstants();
    }
}
