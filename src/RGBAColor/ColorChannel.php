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

use AppUtils\RGBAColor\ColorChannel\DecimalChannel;
use AppUtils\RGBAColor\ColorChannel\EightBitChannel;
use AppUtils\RGBAColor\ColorChannel\HexadecimalChannel;
use AppUtils\RGBAColor\ColorChannel\PercentChannel;
use AppUtils\RGBAColor\ColorChannel\SevenBitChannel;

/**
 * Abstract base class for individual color channels.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see DecimalChannel
 * @see EightBitChannel
 * @see HexadecimalChannel
 * @see PercentChannel
 * @see SevenBitChannel
 */
abstract class ColorChannel
{
    abstract public function get8Bit() : int;

    abstract public function get7Bit() : int;

    abstract public function getDecimal() : float;

    abstract public function getPercent() : float;

    public function getHexadecimal() : string
    {
        return UnitsConverter::int2hex($this->get8Bit());
    }

    /**
     * @return ColorChannel
     */
    abstract public function invert();

    /**
     * @param string $hex A double or single hex character. e.g. "FF".
     *                    For a single character, it is assumed it should
     *                    be duplicated. Example: "F" > "FF".
     * @return HexadecimalChannel
     * @throws ColorException
     */
    public static function hexadecimal(string $hex) : HexadecimalChannel
    {
        return new HexadecimalChannel($hex);
    }

    public static function eightBit(int $value) : EightBitChannel
    {
        return new EightBitChannel($value);
    }

    public static function sevenBit(int $value) : SevenBitChannel
    {
        return new SevenBitChannel($value);
    }

    public static function percent(float $percent) : PercentChannel
    {
        return new PercentChannel($percent);
    }

    public static function decimal(float $opacity) : DecimalChannel
    {
        return new DecimalChannel($opacity);
    }
}
