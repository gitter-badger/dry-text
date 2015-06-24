<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 24.06.15
 * Time: 1:08
 * Project: dry-text
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\DryText;


class DryString
{

    public static $encodingDetection = array('windows-1251', 'koi8-r', 'iso8859-5');

    protected static $ABCCyrillicToLatin;
    protected static $ABCLatinToCyrillic;

    /**
     * Разбивает на массив текст заданной величина скрипт вырезает с сохранением предложений
     * @param string $text     разбиваемый текст
     * @param int    $partSize Максимальное количество символов в одной части
     * @param int    $offset   максимальное количество частей 0 = бесконечно
     * @return array
     */
    public static function divideText($text = "", $partSize = 100, $offset = 0)
    {
        $parts = [];
        if (mb_strlen($text, 'utf-8') >= $partSize) {
            for ($i = 0; ($i < $offset || $offset === 0) && $text; $i++) {
                $partText = mb_substr($text, 0, $partSize, 'utf-8');
                preg_match('%^(.+[\.\?\!]|$).*%imsuU', $partText, $match);
                if (mb_strlen($match[1], 'utf-8') == 0) {
                    break;
                }
                $parts[] = $match[1];
                $text = trim(preg_replace('%' . preg_quote($match[1], '%') . '%ms', '', $text, 1));
            }
        } else {
            $parts[] = $text;
        }

        return $parts;
    }

    /**
     * Стирание спец. символов, двойных и более пробелов, табуляций и переводов строки
     * @param string       $text
     * @param array|string $repRegExArray массив регулярных выражений для замены на пробел
     * @param string       $repText
     * @return string
     */
    public static function clear($text = "", $repRegExArray = ['%\s+%'], $repText = " ")
    {
        if (is_string($repRegExArray)) {
            $text = preg_replace($repRegExArray, $repText, $text);
        } elseif (is_array($repRegExArray)) {
            foreach ($repRegExArray as $value) {
                $text = preg_replace($value, $repText, $text);
            }
        }

        return $text;
    }

    /**
     * support encoding UTF-8 windows-1251 koi8-r iso8859-5
     * @param string $text строка для определения кодировки
     * @return string имя кодировки
     * @author m00t
     * @url    https://github.com/m00t/detect_encoding
     */
    public static function getEncodingName($text)
    {
        if (mb_detect_encoding($text, array('UTF-8'), true) == 'UTF-8') {
            return 'UTF-8';
        }
        $weights = [];
        $specters = [];
        foreach (self::$encodingDetection as $encoding) {
            $weights[$encoding] = 0;
            $specters[$encoding] = require 'phar://' . __DIR__ . '/specters.phar/' . $encoding . '.php';
        }
        foreach (str_split($text, 2) as $key) {
            foreach (self::$encodingDetection as $encoding) {
                if (isset($specters[$encoding][$key])) {
                    $weights[$encoding] += $specters[$encoding][$key];
                }
            }
        }
        $sumWeight = array_sum($weights);
        foreach ($weights as $encoding => $weight) {
            if (!$sumWeight) {
                $weights[$encoding] = 0;
            } else {
                $weights[$encoding] = $weight / $sumWeight;
            }
        }
        arsort($weights, SORT_NUMERIC);
        $encodingName = key($weights);
        unset($weights, $specters, $text);

        return $encodingName;
    }

    /**
     * ISO 9:1995
     * @param string $text text need encoding utf-8
     * @return string
     */
    public static function translateCyrillicToLatin($text)
    {
        if (!self::$ABCCyrillicToLatin) {
            self::$ABCCyrillicToLatin = require(__DIR__ . '/abc/cyrillic_to_latin.php');
        }

        return self::translateText($text, self::$ABCCyrillicToLatin);
    }

    /**
     * ISO 9:1995
     * @param string $text text need encoding utf-8
     * @return string
     */
    public static function translateLatinToCyrillic($text)
    {
        if (!self::$ABCLatinToCyrillic) {
            self::$ABCLatinToCyrillic = require(__DIR__ . '/abc/latin_to_cyrillic.php');
        }

        return self::translateText($text, self::$ABCLatinToCyrillic);
    }

    protected static function translateText($text, $abc)
    {
        foreach ($abc as $from => $to) {
            $text = preg_replace('%' . preg_quote($from, '%') . '%smu', $to, $text);
            $text = preg_replace('%' . preg_quote($from, '%') . '%ismu', mb_strtolower($to, 'utf-8'), $text);
        }

        return $text;
    }

    /**
     * @param array $text
     * @param bool  $bestKey
     * @return string
     */
    public static function getBiggestString($text, &$bestKey = false)
    {
        $bigA = 0;
        $bigKey = false;
        foreach ($text as $key => $value) {
            $thisA = mb_strlen($value, 'utf-8');
            if ($thisA >= $bigA) {
                $bigA = $thisA;
                $bigKey = $key;
            }
        }
        $bestKey = $bigKey;

        return isset($text[$bigKey]) ? $text[$bigKey] : false;
    }
}