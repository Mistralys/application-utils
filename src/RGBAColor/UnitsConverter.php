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
    private static int $floatPrecision = 2;

    /**
     * @param string $hex
     * @return int 0 to 255
     */
    public static function hex2int(string $hex) : int
    {
        return (int)hexdec($hex);
    }

    /**
     * Converts a color value to a percentage.
     * @param int $eightBit 0 to 255
     * @return float 0 to 100
     */
    public static function intEightBit2Percent(int $eightBit) : float
    {
        return $eightBit * 100 / 255;
    }

    /**
     * @param int $colorValue 0 to 127
     * @return float 0 to 100
     */
    public static function intSevenBit2Percent(int $colorValue) : float
    {
        return $colorValue * 100 / 127;
    }

    /**
     * Converts a percentage to an integer color value.
     * @param float $percent 0 to 100
     * @return int 0 to 255
     */
    public static function percent2IntEightBit(float $percent) : int
    {
        $value = $percent * 255 / 100;
        return (int)round($value, 0, PHP_ROUND_HALF_UP);
    }

    /**
     * @param float $percent 0 to 100
     * @return int 0 to 127
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
     * @param int $eightBit 255
     * @return float 0.0 to 1.0
     */
    public static function intEightBit2Alpha(int $eightBit) : float
    {
        return round($eightBit / 255, self::$floatPrecision);
    }

    /**
     * @param int $eightBit 0 to 255
     * @return int 0 to 127
     */
    public static function intEightBit2IntSevenBit(int $eightBit) : int
    {
        return (int)round($eightBit * 127 / 255);
    }

    /**
     * @param int $sevenBit 0 to 127
     * @return int 0 to 255
     */
    public static function intSevenBit2IntEightBit(int $sevenBit) : int
    {
        return (int)round($sevenBit * 255 / 127);
    }

    /**
     * @param int $sevenBit 0 to 127
     * @return float 0.0 to 1.0
     */
    public static function intSevenBit2Alpha(int $sevenBit) : float
    {
        return round($sevenBit / 127, self::$floatPrecision);
    }

    /**
     * @param float $alpha 0.0 to 1.0
     * @return int 0 to 255
     */
    public static function alpha2IntEightBit(float $alpha) : int
    {
        return (int)round($alpha * 255);
    }

    /**
     * @param float $alpha 0.0 to 1.0
     * @return int 0 to 127
     */
    public static function alpha2IntSevenBit(float $alpha) : int
    {
        return (int)round($alpha * 127);
    }

    /**
     * @param float $percent 0-100
     * @return float 0.0-1.0
     */
    public static function percent2Alpha(float $percent) : float
    {
        return round($percent/100, self::$floatPrecision);
    }

    /**
     * @param float $alpha 0.0-1.0
     * @return float 0-100
     */
    public static function alpha2percent(float $alpha) : float
    {
        return $alpha * 100;
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
            $str = str_pad($str, 2, '0', STR_PAD_LEFT);
        }

        return strtoupper($str);
    }

    /**
     * @param float $hue 0-360
     * @return int 127
     */
    public static function hue2IntSevenBit(float $hue) : int
    {
        return (int)round($hue * 360 / 127);
    }

    /**
     * @param float $hue 0-360
     * @return int 0-255
     */
    public static function hue2IntEightBit(float $hue) : int
    {
        return (int)round($hue * 360 / 255);
    }

    /**
     * @param float $hue 0-360
     * @return float 0.0-1.0
     */
    public static function hue2Alpha(float $hue) : float
    {
        return round($hue / 360, self::$floatPrecision);
    }

    /**
     * @param float $hue 0-360
     * @return float 0-100
     */
    public static function hue2Percent(float $hue) : float
    {
        return $hue * 100 / 360;
    }
}
