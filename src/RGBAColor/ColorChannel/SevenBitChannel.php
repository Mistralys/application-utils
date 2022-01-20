<?php

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\UnitsConverter;

class SevenBitChannel extends ColorChannel
{
    /**
     * @var int
     */
    private $value;

    /**
     * @param int $value 0-127
     */
    public function __construct(int $value)
    {
        $this->value = $value;
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
    public function getFloat() : float
    {
        return UnitsConverter::intSevenBit2Float($this->value);
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
        return ColorChannel::SevenBit(127-$this->value);
    }
}
