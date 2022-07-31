<?php
/**
 * File containing the class {@see FormatsConverter}.
 *
 * @see FormatsConverter
 *@subpackage RGBAColor
 * @package AppUtils
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\FormatsConverter\HEXParser;
use function AppUtils\parseVariable;

/**
 * The converter static class is used to convert between color
 * information formats.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FormatsConverter
{
    public const ERROR_INVALID_COLOR_ARRAY = 99701;

    /**
     * Converts the color to a HEX color value. This is either
     * a RRGGBB or RRGGBBAA string, depending on whether there
     * is an alpha channel value.
     *
     * NOTE: The HEX letters are always uppercase.
     *
     * @param RGBAColor $color
     * @return string
     */
    public static function color2HEX(RGBAColor $color) : string
    {
        $hex =
            UnitsConverter::int2hex($color->getRed()->get8Bit()) .
            UnitsConverter::int2hex($color->getGreen()->get8Bit()) .
            UnitsConverter::int2hex($color->getBlue()->get8Bit());

        if($color->hasTransparency())
        {
            $hex .= UnitsConverter::int2hex($color->getAlpha()->get8Bit());
        }

        return strtoupper($hex);
    }

    /**
     * Converts the color to a CSS `rgb()` or `rgba()` value.
     *
     * @param RGBAColor $color
     * @return string
     */
    public static function color2CSS(RGBAColor $color) : string
    {
        if($color->hasTransparency())
        {
            return sprintf(
                'rgba(%s, %s, %s, %s)',
                $color->getRed()->get8Bit(),
                $color->getGreen()->get8Bit(),
                $color->getBlue()->get8Bit(),
                $color->getAlpha()->getDecimal()
            );
        }

        return sprintf(
            'rgb(%s, %s, %s)',
            $color->getRed()->get8Bit(),
            $color->getGreen()->get8Bit(),
            $color->getBlue()->get8Bit()
        );
    }

    public static function color2array(RGBAColor $color) : ArrayConverter
    {
        return new ArrayConverter($color);
    }

    /**
     * Checks if the array is a valid color array with
     * all expected color keys present. The `alpha` key
     * is optional. If it's not valid, throws an exception.
     *
     * @param array<string|int,int|float> $color
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_COLOR_ARRAY
     */
    public static function requireValidColorArray(array $color) : void
    {
        if(self::isColorArray($color))
        {
            return;
        }

        throw new ColorException(
            'Not a valid color array.',
            sprintf(
                'The color array is in the wrong format, or is missing required keys. '.
                'Given: '.PHP_EOL.
                '%s',
                parseVariable($color)->toString()
            ),
            RGBAColor::ERROR_INVALID_COLOR_ARRAY
        );
    }

    /**
     * Checks whether the specified array contains all required
     * color keys.
     *
     * @param array<string|int,int|float> $color
     * @return bool
     */
    public static function isColorArray(array $color) : bool
    {
        $keys = array(
            RGBAColor::CHANNEL_RED,
            RGBAColor::CHANNEL_GREEN,
            RGBAColor::CHANNEL_BLUE
        );

        foreach($keys as $key)
        {
            if(!isset($color[$key]))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Human-readable label of the color. Automatically
     * switches between RGBA and RGB depending on whether
     * the color has any transparency.
     *
     * @param RGBAColor $color
     * @return string
     */
    public static function color2readable(RGBAColor $color) : string
    {
        if($color->hasTransparency())
        {
            return sprintf(
                'RGBA(%s %s %s %s)',
                $color->getRed()->get8Bit(),
                $color->getGreen()->get8Bit(),
                $color->getBlue()->get8Bit(),
                $color->getAlpha()->get8Bit()
            );
        }

        return sprintf(
            'RGB(%s %s %s)',
            $color->getRed()->get8Bit(),
            $color->getGreen()->get8Bit(),
            $color->getBlue()->get8Bit()
        );
    }

    /**
     * @var HEXParser|NULL
     */
    private static $hexParser = null;

    /**
     * Parses a HEX color value, and converts it to
     * an RGBA color array.
     *
     * Examples:
     *
     * <pre>
     * $color = RGBAColor_Utilities::parseHexColor('CCC');
     * $color = RGBAColor_Utilities::parseHexColor('CCDDEE');
     * $color = RGBAColor_Utilities::parseHexColor('CCDDEEFA');
     * </pre>
     *
     * @param string $hex
     * @param string $name
     * @return RGBAColor
     *
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_HEX_LENGTH
     */
    public static function hex2color(string $hex, string $name='') : RGBAColor
    {
        if(!isset(self::$hexParser))
        {
            self::$hexParser = new HEXParser();
        }

        return self::$hexParser->parse($hex, $name);
    }

    /**
     * @var array<int,array{key:string,mandatory:bool}>
     */
    private static $keys = array(
        array(
            'key' => RGBAColor::CHANNEL_RED,
            'mandatory' => true
        ),
        array(
            'key' => RGBAColor::CHANNEL_GREEN,
            'mandatory' => true
        ),
        array(
            'key' => RGBAColor::CHANNEL_BLUE,
            'mandatory' => true
        ),
        array(
            'key' => RGBAColor::CHANNEL_ALPHA,
            'mandatory' => false
        )
    );

    /**
     * Converts a color array to an associative color
     * array. Works with indexed color arrays, as well
     * as arrays that are already associative.
     *
     * Expects the color values to always be in the same order:
     *
     * - red
     * - green
     * - blue
     * - alpha (optional)
     *
     * @param array<int|string,int|float> $color
     * @return array<string,int|float>
     *
     * @throws ColorException
     * @see FormatsConverter::ERROR_INVALID_COLOR_ARRAY
     */
    public static function array2associative(array $color) : array
    {
        // If one associative key is present, we assume
        // that the color array is already correct.
        if(isset($color[RGBAColor::CHANNEL_RED]))
        {
            return $color;
        }

        $values = array_values($color);
        $result = array();

        foreach(self::$keys as $idx => $def)
        {
            if(isset($values[$idx]))
            {
                $result[$def['key']] = $values[$idx];
                continue;
            }

            if(!$def['mandatory'])
            {
                continue;
            }

            throw new ColorException(
                'Invalid color array',
                sprintf(
                    'The value for [%s] is missing at index [%s] in the source array, and it is mandatory.',
                    $def['key'],
                    $idx
                ),
                self::ERROR_INVALID_COLOR_ARRAY
            );
        }

        return $result;
    }
}
