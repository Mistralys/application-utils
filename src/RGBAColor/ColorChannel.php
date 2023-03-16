<?php
/**
 * File containing the class {@see \AppUtils\RGBAColor\ColorChannel}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\ColorChannel
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor\ColorChannel\AlphaChannel;
use AppUtils\RGBAColor\ColorChannel\EightBitChannel;
use AppUtils\RGBAColor\ColorChannel\HexadecimalChannel;
use AppUtils\RGBAColor\ColorChannel\HueChannel;
use AppUtils\RGBAColor\ColorChannel\BrightnessChannel;
use AppUtils\RGBAColor\ColorChannel\PercentChannel;
use AppUtils\RGBAColor\ColorChannel\SaturationChannel;
use AppUtils\RGBAColor\ColorChannel\SevenBitChannel;

/**
 * Abstract base class for individual color channels.
 * Acts as factory class for channels with the following
 * methods:
 *
 * - {@see self::hexadecimal()}
 * - {@see self::eightBit()}
 * - {@see self::sevenBit()}
 * - {@see self::percent()}
 * - {@see self::alpha()}
 * - {@see self::hue()}
 * - {@see self::saturation()}
 * - {@see self::brightness()}
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see AlphaChannel
 * @see EightBitChannel
 * @see HexadecimalChannel
 * @see PercentChannel
 * @see SevenBitChannel
 */
abstract class ColorChannel
{
    /**
     * @return mixed
     */
    abstract public function getValue();


    /**
     * @return int 0 to 255
     */
    abstract public function get8Bit() : int;

    /**
     * @return int 0 to 127
     */
    abstract public function get7Bit() : int;

    /**
     * @return float 0.0 to 1.0
     */
    abstract public function getAlpha() : float;

    /**
     * @return float 0 to 100
     */
    abstract public function getPercent() : float;

    public function getPercentRounded() : int
    {
        return (int)round($this->getPercent());
    }

    public function getHexadecimal() : string
    {
        return UnitsConverter::int2hex($this->get8Bit());
    }

    /**
     * @return ColorChannel
     */
    abstract public function invert() : ColorChannel;

    /**
     * @param string|HexadecimalChannel $hex A double or single hex character. e.g. "FF".
     *                    For a single character, it is assumed it should
     *                    be duplicated. Example: "F" > "FF". If an existing hexadecimal
     *                    channel instance is given, this is returned.
     * @return HexadecimalChannel
     * @throws ColorException
     */
    public static function hexadecimal($hex) : HexadecimalChannel
    {
        if($hex instanceof HexadecimalChannel) {
            return $hex;
        }

        return new HexadecimalChannel($hex);
    }

    /**
     * @param int|EightBitChannel $value 0 to 255
     * @return EightBitChannel
     */
    public static function eightBit($value) : EightBitChannel
    {
        if($value instanceof EightBitChannel) {
            return $value;
        }

        return new EightBitChannel($value);
    }

    /**
     * @param int|SevenBitChannel $value 0 to 127
     * @return SevenBitChannel
     */
    public static function sevenBit($value) : SevenBitChannel
    {
        if($value instanceof SevenBitChannel) {
            return $value;
        }

        return new SevenBitChannel($value);
    }

    /**
     * @param int|float|PercentChannel $percent 0 to 100
     * @return PercentChannel
     */
    public static function percent($percent) : PercentChannel
    {
        if($percent instanceof PercentChannel) {
            return $percent;
        }

        return new PercentChannel($percent);
    }

    /**
     * @param float|AlphaChannel|NULL $alpha 0.0 to 1.0
     * @return AlphaChannel
     */
    public static function alpha($alpha) : AlphaChannel
    {
        if($alpha instanceof AlphaChannel) {
            return $alpha;
        }

        if($alpha === null) {
            $alpha = 0.0;
        }

        return new AlphaChannel($alpha);
    }

    /**
     * @param int|float|HueChannel $hue 0 to 360
     * @return HueChannel
     */
    public static function hue($hue) : HueChannel
    {
        if($hue instanceof HueChannel) {
            return $hue;
        }

        return new HueChannel($hue);
    }

    /**
     * @param int|float|BrightnessChannel $brightness 0 to 100
     * @return BrightnessChannel
     */
    public static function brightness($brightness) : BrightnessChannel
    {
        if($brightness instanceof BrightnessChannel) {
            return $brightness;
        }

        return new BrightnessChannel($brightness);
    }

    /**
     * @param int|float|SaturationChannel $saturation 0 to 100
     * @return SaturationChannel
     */
    public static function saturation($saturation) : SaturationChannel
    {
        if($saturation instanceof SaturationChannel) {
            return $saturation;
        }

        return new SaturationChannel($saturation);
    }
}
