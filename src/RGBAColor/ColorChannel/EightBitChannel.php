<?php
/**
 * @package Application Utils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\ColorChannel\EightBitChannel
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\UnitsConverter;

/**
 * Color channel with values from 0 to 255.
 *
 * Native value: {@see self::get8Bit()} and
 * {@see self::getHexadecimal()}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class EightBitChannel extends ColorChannel
{
    public const VALUE_MIN = 0;
    public const VALUE_MAX = 255;

    /**
     * @var int
     */
    private int $value;

    public function __construct(int $value)
    {
        if($value < self::VALUE_MIN) { $value = self::VALUE_MIN; }
        if($value > self::VALUE_MAX) { $value = self::VALUE_MAX; }

        $this->value = $value;
    }

    /**
     * @return int 0-255
     */
    public function getValue() : int
    {
        return $this->value;
    }

    public function get8Bit() : int
    {
        return $this->value;
    }

    public function get7Bit() : int
    {
        return UnitsConverter::intEightBit2IntSevenBit($this->value);
    }

    public function getAlpha() : float
    {
        return UnitsConverter::intEightBit2Float($this->value);
    }

    public function getPercent() : float
    {
        return UnitsConverter::intEightBit2Percent($this->value);
    }

    public function invert() : EightBitChannel
    {
        return ColorChannel::eightBit(255-$this->value);
    }
}
