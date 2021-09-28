<?php
/**
 * File containing the class {@see RGBAColor_Converter}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @see RGBAColor_Converter
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * The converter static class is used to convert between color
 * information formats.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RGBAColor_Converter
{
    /**
     * Converts the color to a HEX color value. This is either
     * a RRGGBB or RRGGBBAA string, depending on whether there
     * is an alpha channel value.
     *
     * NOTE: The HEX letters are always uppercase.
     *
     * @param RGBAColor $color
     * @return string
     * @throws RGBAColor_Exception
     */
    public static function color2HEX(RGBAColor $color) : string
    {
        $hex =
            self::int2hex($color->getRed()).
            self::int2hex($color->getGreen()).
            self::int2hex($color->getBlue());

        if($color->hasTransparency())
        {
            $hex .= self::int2hex($color->getAlpha());
        }

        return strtoupper($hex);
    }

    /**
     * Converts an integer to a HEX color string. This differs
     * from the native `dechex()` function, in that it will
     * return `00` for the color string instead of the default
     * `0` provided by `dechex()`.
     *
     * @param int $int
     * @return string
     */
    private static function int2hex(int $int) : string
    {
        $str = dechex($int);
        if(strlen($str) === 1)
        {
            $str = $str.$str;
        }

        return $str;
    }

    /**
     * Human-readable label of the color. Automatically
     * switches between RGBA and RGB depending on whether
     * the color has any transparency.
     *
     * @param RGBAColor $color
     * @return string
     * @throws RGBAColor_Exception
     */
    public static function color2readable(RGBAColor $color) : string
    {
        if($color->hasTransparency())
        {
            return sprintf(
                'RGBA(%s %s %s %s)',
                $color->getRed(),
                $color->getGreen(),
                $color->getBlue(),
                $color->getAlpha()
            );
        }

        return sprintf(
            'RGB(%s %s %s)',
            $color->getRed(),
            $color->getGreen(),
            $color->getBlue()
        );
    }

    /**
     * Converts a color value to a percentage.
     * @param int $colorValue 0-255
     * @return float
     */
    public static function int2percent(int $colorValue) : float
    {
        return $colorValue * 100 / 255;
    }

    /**
     * Converts a percentage to an integer color value.
     * @param float $percent
     * @return int 0-255
     */
    public static function percent2int(float $percent) : int
    {
        $value = $percent * 255 / 100;
        return intval(round($value, 0, PHP_ROUND_HALF_UP));
    }

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
     * @return array{red:int,green:int,blue:int,alpha:int}
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_HEX_LENGTH
     */
    public static function hex2color(string $hex) : array
    {
        $hex = ltrim($hex, '#'); // Remove the hash if present
        $hex = strtoupper($hex);
        $length = strlen($hex);

        if($length === 3)
        {
            return self::parseHEX3($hex);
        }
        else if($length === 6)
        {
            return self::parseHEX6($hex);
        }
        else if ($length === 8)
        {
            return self::parseHEX8($hex);
        }

        throw new RGBAColor_Exception(
            'Invalid HEX color value.',
            sprintf(
                'The hex string [%s] has an invalid length ([%s] characters). '.
                'It must be either 6 characters (RRGGBB) or 8 characters (RRGGBBAA) long.',
                $hex,
                $length
            ),
            RGBAColor::ERROR_INVALID_HEX_LENGTH
        );
    }

    /**
     * Parses a three-letter HEX color string to a color array.
     *
     * @param string $hex
     * @return array{red:int,green:int,blue:int,alpha:int}
     */
    private static function parseHEX3(string $hex) : array
    {
        return array(
            RGBAColor::COMPONENT_RED => hexdec(str_repeat(substr($hex, 0, 1), 2)),
            RGBAColor::COMPONENT_GREEN => hexdec(str_repeat(substr($hex, 1, 1), 2)),
            RGBAColor::COMPONENT_BLUE => hexdec(str_repeat(substr($hex, 2, 1), 2)),
            RGBAColor::COMPONENT_ALPHA => 255
        );
    }

    /**
     * Parses a six-letter HEX color string to a color array.
     * @param string $hex
     * @return array{red:int,green:int,blue:int,alpha:int}
     */
    private static function parseHEX6(string $hex) : array
    {
        return array(
            RGBAColor::COMPONENT_RED => hexdec(substr($hex, 0, 2)),
            RGBAColor::COMPONENT_GREEN => hexdec(substr($hex, 2, 2)),
            RGBAColor::COMPONENT_BLUE => hexdec(substr($hex, 4, 2)),
            RGBAColor::COMPONENT_ALPHA => 255
        );
    }

    /**
     * Parses an eight-letter HEX color string (with alpha) to a color array.
     * @param string $hex
     * @return array{red:int,green:int,blue:int,alpha:int}
     */
    private static function parseHEX8(string $hex) : array
    {
        return array(
            RGBAColor::COMPONENT_RED => hexdec(substr($hex, 0, 2)),
            RGBAColor::COMPONENT_GREEN => hexdec(substr($hex, 2, 2)),
            RGBAColor::COMPONENT_BLUE => hexdec(substr($hex, 4, 2)),
            RGBAColor::COMPONENT_ALPHA => hexdec(substr($hex, 6, 2))
        );
    }
}
