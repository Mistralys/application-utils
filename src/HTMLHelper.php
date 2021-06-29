<?php

declare(strict_types=1);

namespace AppUtils;

class HTMLHelper
{
    public static function stripComments(string $html) : string
    {
        return preg_replace('/<!--(?!<!)[^\[>].*?-->/si', '', $html);
    }

    private static $newParaTags = array(
        'ul',
        'ol',
        'iframe',
        'table'
    );

    /**
     * Injects the target text at the end of an HTML snippet,
     * either in an existing <p> tag, or in a new <p> tag if
     * the last block tag cannot be used (<ul> for example).
     *
     * NOTE: Assumes that it is not a whole HTML document.
     *
     * @param string $text
     * @param string $html
     * @return string
     */
    public static function injectAtEnd(string $text, string $html) : string
    {
        preg_match_all('%<([A-Z][A-Z0-9]*)\b[^>]*>(.*?)</\1>%si', $html, $result, PREG_PATTERN_ORDER);

        if(empty($result[1])) {
            return '<p>'.$text.'</p>';
        }

        $tagName = array_pop($result[1]);
        $pos = strrpos($html, '</'.$tagName.'>');

        if(in_array(strtolower($tagName), self::$newParaTags)) {
            $replace = '</'.$tagName.'><p>'.$text.'</p>';
        } else {
            $replace = $text.'</'.$tagName.'>';
        }

        return substr_replace($html, $replace, $pos, strlen($html));
    }
}
