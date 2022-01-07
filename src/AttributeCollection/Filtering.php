<?php
/**
 * File containing the class {@see \AppUtils\AttributeCollection\Filtering}.
 *
 * @package AppUtils
 * @subpackage HTML
 * @see \AppUtils\AttributeCollection\Filtering
 */

declare(strict_types=1);

namespace AppUtils\AttributeCollection;

/**
 * Filtering methods for attribute values.
 *
 * @package AppUtils
 * @subpackage HTML
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Filtering
{
    /**
     * Escapes double quotes in an attribute value by replacing
     * them with HTML entities.
     *
     * @param string $value
     * @return string
     */
    public static function quotes(string $value) : string
    {
        return str_replace('"', '&quot;', $value);
    }

    /**
     * Normalizes ampersands, keeping already encoded ones.
     *
     * @param string $url
     * @return string
     */
    public static function URL(string $url) : string
    {
        return str_replace(
            array('&amp;', '&', '__AMP__'),
            array('__AMP__', '__AMP__', '&amp;'),
            $url
        );
    }
}
