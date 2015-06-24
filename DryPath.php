<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 24.06.15
 * Time: 1:09
 * Project: dry-text
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\DryText;


class DryPath
{

    protected static $ipRegEx = '(?<address>(?<ips>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\:?(?<port>\d{1,5})?)';

    /**
     * Аналог встроенней функцией parse_url + pathinfo но с дополнительным разбитием на масив параметры query и fragment и path
     * @param $url
     * @return array
     * scheme Протокол
     * host имя хоста
     * domain домен второго уровня
     * port порт
     * user имя пользователя
     * pass пароль пользователя
     * path полный адрес с именем файла
     * query массив GET запроса [Имя переменной]=Значение
     * fragment массив ссылок на HTML якоря [Имя якоря]=Значение
     */
    public static function parsePath($url)
    {
        $partUrl = parse_url($url);
        if (isset($partUrl['query'])) {
            self::explodeQuery($partUrl['query']);
        }
        if (isset($partUrl['fragment'])) {
            self::explodeQuery($partUrl['fragment']);
        }
        if (isset($partUrl['path'])) {
            $partPath = pathinfo($partUrl['path']);
            $partUrl['dirname'] = isset($partPath['dirname']) ? $partPath['dirname'] : '';
            $partUrl['basename'] = isset($partPath['basename']) ? $partPath['basename'] : '';
            $partUrl['extension'] = isset($partPath['extension']) ? $partPath['extension'] : '';
            $partUrl['filename'] = isset($partPath['filename']) ? $partPath['filename'] : '';
        }

        return $partUrl;
    }

    protected static function explodeQuery(&$query)
    {
        $arrayFragment = explode('&', $query);
        $query = [];
        foreach ($arrayFragment as $value) {
            $part = explode("=", $value);
            $query[$part[0]] = (isset($part[1]) ? $part[1] : '');
        }
    }

    public static function parseUrl($url)
    {
        return self::parsePath($url);
    }

    public static function checkUrlProtocol($url)
    {
        if (!preg_match("%^(http|https|ftp)://%iUm", $url)) {
            $url = "http://" . $url;
        }

        return $url;
    }

    /**
     * @param string $url исходный адрес
     * @param int    $level
     * @return bool|string
     */
    public static function getDomainName($url, $level = 2)
    {
        $partUrl = self::parsePath($url);
        $levelRegEx = [];
        for ($i = 0; $i < $level; $i++) {
            $levelRegEx[] = '[^\.]+';
        }
        $fullDomain = isset($partUrl['host']) ? $partUrl['host'] : $partUrl['path'];

        return preg_match('%(?<domain>' . implode('\.', $levelRegEx) . ')($|/|\s)%ims', $fullDomain, $match) ? $match['domain'] : false;
    }

    /**
     * Проверяет строку на соответствие шаблону ip адреса с портом
     * @param $str
     * @return bool
     */
    public static function isIp($str)
    {
        return (bool)preg_match('%^' . self::$ipRegEx . '$%', $str);
    }

    public static function getIp($str)
    {
        if (preg_match_all('%' . self::$ipRegEx . '%ms', $str, $matches)) {
            return $matches['address'];
        }

        return [];
    }
}