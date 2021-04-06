<?php
/**
 * File containing the {@see AppUtils\URLInfo_Filter} class.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @see AppUtils\URLInfo_Filter
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Static class with utility methods to handle filtering 
 * an URL string, by removing any unwanted special characters 
 * and the like.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLInfo_Filter
{
    public static function filter(string $url) : string
    {
        // fix ampersands if it comes from HTML
        $url = str_replace('&amp;', '&', $url);

        // In the case of tel URLs, we convert the syntax to use double
        // slashes to make them parsable.
        if(strstr($url, 'tel:') !== false && strstr($url, 'tel://') === false) {
            $url = str_replace('tel:', 'tel://', $url);
        }

        // we remove any control characters from the URL, since these
        // may be copied when copy+pasting from word or pdf documents
        // for example.
        $url = \AppUtils\ConvertHelper::stripControlCharacters($url);
        
        // fix the pesky unicode hyphen that looks like a regular hyphen,
        // but isn't and can cause all sorts of problems
        $url = str_replace('‐', '-', $url);
        
        // remove newlines and tabs
        $url = str_replace(array("\n", "\r", "\t"), '', $url);
        
        $url = trim($url);
        
        return $url;
    }
}
