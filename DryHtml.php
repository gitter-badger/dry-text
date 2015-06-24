<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 24.06.15
 * Time: 1:03
 * Project: dry-text
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\DryText;


class DryHtml
{
    /**
     * Массив с тегами и хеш кодами для обработки через синонимайзер или переводчик, чтоб не потерять HTML теги
     * $cryptTags['tag'] набор тегов
     * $cryptTags['hash'] набор хешей
     * Порядок тегов и хешей соответстует их положению в строке.
     * @var array
     */
    protected $cryptTags;

    /**
     * Заменяет HTML код  на хеши, чтоб при пропуске через спец программы(синонимайзей, переводчик) не потерять теги
     * @param string $text шифруемый текст
     * @param string $reg  регулярное выражение для поиска шифруемых данных
     * @return string
     */
    public function encryptTag($text, $reg = "%(<[^<>]*>)%iUsm")
    {
        $count = preg_match_all($reg, $text, $matches);
        for ($i = 0; $i < $count; $i++) {
            $str = $matches[0][$i];
            $this->cryptTags['hash'][$i] = " " . microtime(1) . mt_rand() . " ";
            $this->cryptTags['tag'][$i] = $str;
            $text = preg_replace("%" . preg_quote($this->cryptTags['tag'][$i], '%') . "%ms", $this->cryptTags['hash'][$i], $text);
        }

        return $text;
    }

    /**
     * Заменяет хеш на HTML код после обработки через функцию encryptTag
     * @param string $text текст с хешами
     * @return string
     */
    public function decryptTag($text)
    {
        foreach ($this->cryptTags['hash'] as $key => $hash) {
            $text = preg_replace("%" . preg_quote($hash, '%') . "%ms", $this->cryptTags['tag'][$key], $text);
        }

        return $text;
    }

    /**
     * @return array
     */
    public function getCryptTags()
    {
        return $this->cryptTags;
    }

    /**
     * Парсит html страницу и вытаскивает содержимое тега
     * @param string $text       текст в котором ищет
     * @param string $startTag   открывающий тег
     * @param bool   $withoutTag возвращать с тегом или без
     * @param string $encoding
     * @return string
     */
    public static function betweenTag($text, $startTag = '<div class="xxx">', $withoutTag = true, $encoding = "UTF-8")
    {
        $tagName = self::getTagName($text, $startTag);
        if (!$tagName) {
            return false;
        }
        $startPos = mb_strpos($text, $startTag, 0, $encoding);
        $text = mb_substr($text, $startPos, -1, $encoding);
        $posEnd = self::getClosingTagPosition($text, $tagName, $encoding);

        return self::cutTagText($text, $startTag, $tagName, $posEnd, $withoutTag, $encoding);
    }

    protected static function getTagName($text, &$startTag)
    {
        if (!preg_match('%<(?<tag>\w+)[^>]*>%im', $startTag, $tag)) {
            return false;
        }
        if (preg_match('%<(?<tag>\w+)\s*[\w-]+=\s*[\"\']?[^\'\"]+[\"\']?[^>]*>%im', $startTag)) {
            preg_match_all('%(?<parametr>[\w-]+=([\"\'][^\'\">]*[\"\']|[\"\']?[^\'\">]*[\"\']?))%im', $startTag, $matches);
            $reg = '%<' . preg_quote($tag["tag"], '%');
            foreach ($matches['parametr'] as $value) {
                $reg .= '[^>]*' . preg_quote($value, '%') . '[^>]*';
            }
            $reg .= '>%im';
            if (!preg_match($reg, $text, $match)) {
                return false;
            }
            $startTag = $match[0];
        } else {
            preg_match('%<(?<tag>\w+)[^>]*>%i', $startTag, $tag);
            preg_match('%<(?<tag>' . preg_quote($tag['tag'], '%') . ')[^>]*>%i', $text, $tag);
        }

        return $tag['tag'];
    }

    protected static function getClosingTagPosition($text, $tagName, $encoding)
    {
        $openTag = "<" . $tagName;
        $closeTag = "</" . $tagName;
        $countOpenTag = 0;
        $posEnd = 0;
        $countTag = 2 * preg_match_all('%' . preg_quote($openTag, '%') . '%ims', $text);
        for ($i = 0; $i < $countTag; $i++) {
            $posOpenTag = mb_strpos($text, $openTag, $posEnd, $encoding);
            $posCloseTag = mb_strpos($text, $closeTag, $posEnd, $encoding);
            if ($posOpenTag === false) {
                $posOpenTag = $posCloseTag + 1;
            }
            if ($posOpenTag < $posCloseTag) {
                $countOpenTag++;
                $posEnd += $posOpenTag + 1 - $posEnd;
            } else {
                $countOpenTag--;
                $posEnd += $posCloseTag + 1 - $posEnd;
            }
            if (!$countOpenTag) {
                break;
            }
        }

        return $posEnd;
    }

    protected static function cutTagText($text, $startTag, $tagName, $posEnd, $withoutTag, $encoding)
    {
        if ($withoutTag) {
            $start = mb_strlen($startTag, $encoding);
            $length = $posEnd - mb_strlen($startTag, $encoding) - 1;
        } else {
            $start = 0;
            $length = $posEnd + mb_strlen($tagName, $encoding) + 2;
        }

        return mb_substr($text, $start, $length, $encoding);
    }
}