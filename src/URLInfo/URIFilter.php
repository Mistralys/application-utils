<?php
/**
 * @package Application Utils
 * @subpackage URLInfo
 * @see \AppUtils\URLInfo\URIFilter
 */

declare(strict_types=1);

namespace AppUtils\URLInfo;

use AppUtils\ConvertHelper;

/**
 * Static class with utility methods to handle filtering
 * a URL string, by removing any unwanted special characters
 * and the like.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URIFilter
{
    public static function filter(string $url) : string
    {
        // fix ampersands if it comes from HTML
        $url = str_replace('&amp;', '&', $url);

        // In the case of tel URLs, we convert the syntax to use double
        // slashes to make them parsable.
        if (stripos($url, 'tel:') !== false && stripos($url, 'tel://') === false)
        {
            $url = str_replace('tel:', 'tel://', $url);
        }

        // we remove any control characters from the URL, since these
        // may be copied when copy+pasting from word or pdf documents
        // for example.
        $url = ConvertHelper::stripControlCharacters($url);

        // fix the pesky unicode hyphen that looks like a regular hyphen,
        // but isn't and can cause all sorts of problems
        $url = str_replace('‐', '-', $url);

        // remove url encoded and characters of newlines and tabs
        $url = str_replace(array("\n", "\r", "\t", '%0D', '%0A', '%09'), '', $url);

        return trim($url);
    }
}
