<?php

namespace MailService\MailService\Tests;

use ReflectionClass;
use ReflectionException;

class TestHelper
{
    /**
     * Get private property from passing object and property name
     * @param object $object
     * @param string $propName
     * @return mixed
     * @throws ReflectionException
     */
    static function getPrivatePropValue(object $object, string $propName): mixed
    {
        $reflect = new ReflectionClass($object);
        $prop = $reflect->getProperty($propName);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }
}