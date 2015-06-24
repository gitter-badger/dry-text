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

class DryHtmlTest extends PHPUnit_Framework_TestCase
{
    public static $name;

    public static function setUpBeforeClass()
    {
        self::$name = 'unit_test';
    }

    /**
     * @param        $name
     * @param string $className
     * @return \ReflectionMethod
     */
    protected static function getMethod($name, $className = 'bpteam\DryText\DryHtml')
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
    protected static function getProperty($name, $className = 'bpteam\DryText\DryHtml')
    {
        $class = new ReflectionClass($className);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    function testEncryptDecryptTag(){
        $text = 'Hello</br><h1>WOW</h1><p>in teg text</p>';
        $dry = new DryHtml();
        $encryptText = $dry->encryptTag($text);
        $decryptText = $dry->decryptTag($encryptText);
        $this->assertEquals($text, $decryptText);
    }

    function testBetweenTag(){
        $text = '<p>test</p><div>I am test <div class="test">Hi<div> you are cool
Проверка UTF8</div></div>:)</div>';
        $inTag = DryHtml::betweenTag($text, '<div class="test">');
        $withTag = DryHtml::betweenTag($text, '<div class="test">', false);
        $resultInTag = 'Hi<div> you are cool
Проверка UTF8</div>';
        $resultWithTag = '<div class="test">Hi<div> you are cool
Проверка UTF8</div></div>';
        $this->assertEquals($resultInTag, $inTag);
        $this->assertEquals($resultWithTag, $withTag);
    }
}