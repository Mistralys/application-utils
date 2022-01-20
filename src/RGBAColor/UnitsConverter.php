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

/**
 * The converter static class is used to convert between color
 * information formats.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UnitsConverter
{
    private static $floatPrecision = 2;

    /**
     * Converts a color value to a percentage.
     * @param int $colorValue 0-255
     * @return float
     */
    public static function intEightBit2Percent(int $colorValue) : float
    {
        return $colorValue * 100 / 255;
    }

    public static function intSevenBit2Percent(int $colorValue) : float
    {
        return $colorValue * 100 / 127;
    }

    /**
     * Converts a percentage to an integer color value.
     * @param float $percent
     * @return int 0-255
     */
    public static function percent2IntEightBit(float $percent) : int
    {
        $value = $percent * 255 / 100;
        return (int)round($value, 0, PHP_ROUND_HALF_UP);
    }

    /**
     * @param float $percent
     * @return int 0-127
     */
    public static function percent2IntSevenBit(float $percent) : int
    {
        $value = $percent * 127 / 100;
        return (int)round($value, 0, PHP_ROUND_HALF_UP);
    }

    /**
     * Converts an alpha value based on a 0-255 numeric
     * value to a 0-1 based float value.
     *
     * @param int $alpha
     * @return float
     */
    public static function intEightBit2Float(int $alpha) : float
    {
        return round($alpha / 255, self::$floatPrecision);
    }

    public static function intEightBit2IntSevenBit(int $alpha) : int
    {
        return (int)round($alpha * 127 / 255);
    }

    public static function intSevenBit2IntEightBit(int $alpha) : int
    {
        return (int)round($alpha * 255 / 127);
    }

    public static function intSevenBit2Float(int $alpha) : float
    {
        return round($alpha / 127, self::$floatPrecision);
    }

    public static function float2IntEightBit(float $alpha) : int
    {
        return (int)round($alpha * 255);
    }

    public static function float2IntSevenBit(float $alpha) : int
    {
        return (int)round($alpha * 127);
    }

    public static function percent2Float(float $percent) : float
    {
        return round($percent/100, self::$floatPrecision);
    }

    public static function float2percent(float $value) : float
    {
        return $value * 100;
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
    public static function int2hex(int $int) : string
    {
        $str = dechex($int);
        if (strlen($str) === 1)
        {
            $str .= $str;
        }

        return $str;
    }
}
