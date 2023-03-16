<?php
/**
 * @package Application Utils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\ColorChannel\SevenBitChannel
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\UnitsConverter;

/**
 * Color channel with values from 0 to 127.
 *
 * Native value: {@see self::get7Bit()}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class SevenBitChannel extends ColorChannel
{
    public const VALUE_MIN = 0;
    public const VALUE_MAX = 127;

    /**
     * @var int
     */
    private int $value;

    /**
     * @param int $value 0-127
     */
    public function __construct(int $value)
    {
        if($value < self::VALUE_MIN) { $value = self::VALUE_MIN; }
        if($value > self::VALUE_MAX) { $value = self::VALUE_MAX; }

        $this->value = $value;
    }

    public function getValue() : int
    {
        return $this->value;
    }

    /**
     * @return int 0-255
     */
    public function get8Bit() : int
    {
        return UnitsConverter::intSevenBit2IntEightBit($this->value);
    }

    /**
     * @return int 0-127
     */
    public function get7Bit() : int
    {
        return $this->value;
    }

    /**
     * @return float 0-1
     */
    public function getAlpha() : float
    {
        return UnitsConverter::intSevenBit2Alpha($this->value);
    }

    /**
     * @return float 0-100
     */
    public function getPercent() : float
    {
        return UnitsConverter::intSevenBit2Percent($this->value);
    }

    public function invert() : SevenBitChannel
    {
        return ColorChannel::sevenBit(127-$this->value);
    }
}
