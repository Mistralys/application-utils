<?php

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\UnitsConverter;

class EightBitChannel extends ColorChannel
{
    /**
     * @var int
     */
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function get8Bit() : int
    {
        return $this->value;
    }

    public function get7Bit() : int
    {
        return UnitsConverter::intEightBit2IntSevenBit($this->value);
    }

    public function getFloat() : float
    {
        return UnitsConverter::intEightBit2Float($this->value);
    }

    public function getPercent() : float
    {
        return UnitsConverter::intEightBit2Percent($this->value);
    }

    public function invert() : EightBitChannel
    {
        return ColorChannel::EightBit(255-$this->value);
    }
}
