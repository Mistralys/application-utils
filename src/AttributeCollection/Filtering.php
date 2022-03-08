<?php
/**
 * File containing the class {@see \AppUtils\AttributeCollection\Filtering}.
 *
 * @see \AppUtils\AttributeCollection\Filtering
 * @subpackage HTML
 * @package AppUtils
 */

declare(strict_types=1);

namespace AppUtils\AttributeCollection;

use AppUtils\Interface_Stringable;
use AppUtils\StringBuilder_Interface;

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

    /**
     * @param string|number|bool|Interface_Stringable|StringBuilder_Interface|NULL $value
     * @return string
     */
    public static function value2string($value) : string
    {
        if($value === true)
        {
            return 'true';
        }

        if($value === false)
        {
            return 'false';
        }

        return (string)$value;
    }
}
