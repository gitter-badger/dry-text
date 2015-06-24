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

class DryStringTest extends PHPUnit_Framework_TestCase
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
    protected static function getMethod($name, $className = 'bpteam\DryText\DryString')
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
    protected static function getProperty($name, $className = 'bpteam\DryText\DryString')
    {
        $class = new ReflectionClass($className);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    public function testDivideText()
    {
        $textArray = [
            'hello world!',
            'Do you test a function?',
            'Yes, I test the function.',
            'Привет мир!',
            'Ты тестируешь функцию?',
            'Да я тестирую функцию.'
        ];
        $dividedText = DryString::divideText(implode(' ', $textArray), 25);
        foreach ($dividedText as $key => $value) {
            $this->assertEquals($textArray[$key], $dividedText[$key]);
        }
    }

    function testTranslateCyrillicToLatin()
    {
        $text = 'Генадий выпил водки и занялся йогой. Вот алкаш!';
        $translateText = DryString::translateCyrillicToLatin($text);
        $this->assertEquals('Genadij vypil vodki i zanyalsya jogoj. Vot alkash!', $translateText);
    }

    function testClear(){
        $text = '<h1>Hello Мир!!!     			 фывпфывап asdfsd agjas;dgl
	Как<h1> tak!';
        $trueText = ' Hello Мир!!! фывпфывап asdfsd agjas;dgl Как tak!';
        $this->assertEquals($trueText, DryString::clear($text, ['%<[^>]+>%ims', '%\s+%',]));
    }

    function testGetEncodingName(){
        $dir = __DIR__ . '/../support';
        $text_cp1251 = file_get_contents($dir . '/cp1251.txt');
        $text_utf8 = file_get_contents($dir . '/utf8.txt');
        $cp1251 = DryString::getEncodingName($text_cp1251);
        $utf8 = DryString::getEncodingName($text_utf8);
        $this->assertEquals('windows-1251', $cp1251);
        $this->assertEquals('UTF-8', $utf8);
    }
}