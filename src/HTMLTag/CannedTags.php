<?php
/**
 * File containing the class {@see \AppUtils\HTMLTag\CannedTags}.
 *
 * @package AppUtils
 * @subpackage HTML
 * @see \AppUtils\HTMLTag\CannedTags
 */

declare(strict_types=1);

namespace AppUtils\HTMLTag;

use AppUtils\HTMLTag;
use AppUtils\StringBuilder_Interface;

/**
 * Utility class that speeds up creating commonly used
 * HTML tag instances with the `HTMLTag` class.
 *
 * @package AppUtils
 * @subpackage HTML
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class CannedTags
{
    /**
     * @param string $url
     * @param string|number|StringBuilder_Interface|NULL $content
     * @return HTMLTag
     */
    public static function anchor(string $url, $content=null) : HTMLTag
    {
        return HTMLTag::create('a')
            ->href($url)
            ->setContent($content);
    }

    public static function br() : HTMLTag
    {
        return HTMLTag::create('br')
            ->setSelfClosing();
    }

    /**
     * @param string|number|StringBuilder_Interface|NULL $content
     * @return HTMLTag
     */
    public static function div($content=null) : HTMLTag
    {
        return HTMLTag::create('div')->setContent($content);
    }

    /**
     * @param string|number|StringBuilder_Interface|NULL $content
     * @return HTMLTag
     */
    public static function p($content=null) : HTMLTag
    {
        return HTMLTag::create('p')->setContent($content);
    }
}
