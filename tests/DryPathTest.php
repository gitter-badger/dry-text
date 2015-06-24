<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 24.06.15
 * Time: 1:04
 * Project: dry-text
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\DryText;

use \PHPUnit_Framework_Testcase;
use \ReflectionClass;

class DryPathTest extends PHPUnit_Framework_TestCase
{
    public static $name;


    public static function setUpBeforeClass()
    {
        self::markTestSkipped('Make tests, M@%#$!');
        self::$name = 'unit_test';
    }

    /**
     * @param        $name
     * @param string $className
     * @return \ReflectionMethod
     */
    protected static function getMethod($name, $className = 'bpteam\DryText\DryPath')
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @param        $name
     * @param string $className
     * @return \ReflectionProperty
     */
    protected static function getProperty($name, $className = 'bpteam\DryText\DryPath')
    {
        $class = new ReflectionClass($className);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }
}