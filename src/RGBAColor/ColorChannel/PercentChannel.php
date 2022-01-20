<?php

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\UnitsConverter;

class PercentChannel extends ColorChannel
{
    /**
     * @var float
     */
    private $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function getFloat() : float
    {
        return UnitsConverter::percent2Float($this->value);
    }

    public function get8Bit() : int
    {
        return UnitsConverter::percent2IntEightBit($this->value);
    }

    public function get7Bit() : int
    {
        return UnitsConverter::percent2IntSevenBit($this->value);
    }

    public function getPercent() : float
    {
        return $this->value;
    }

    public function invert() : PercentChannel
    {
        return ColorChannel::Percent(100-$this->value);
    }
}
