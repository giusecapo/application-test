<?php

declare(strict_types=1);

namespace App\UnitTests;

use \ReflectionObject as ReflectionObject;
use \ReflectionClass as ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


abstract class AbstractKernelTestCase extends KernelTestCase
{
    /**
     * Set a private property on an object for testing purposes
     * @param object $obj: the object to set the private property on
     * @param string $propName: the name of the private property to set
     * @param mixed $value: the value to set the private property to
     */
    protected static function setPrivateProp(object $obj, string $propName, mixed $value): void
    {
        $reflectionObject = new ReflectionObject($obj);
        $propProperty = $reflectionObject->getProperty($propName);
        $propProperty->setAccessible(true);
        $propProperty->setValue($obj, $value);
    }

    /**
     * Call a private method on an object for testing purposes
     * @param object $obj: the object to call the private method on
     * @param string $methodName: the name of the private method to call
     * @param array $args: the arguments to pass to the private method
     */
    protected static function executePrivateMethod(object $obj, string $methodName, array $args): mixed
    {
        $reflectionClass = new ReflectionClass($obj::class);
        $method = $reflectionClass->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}
