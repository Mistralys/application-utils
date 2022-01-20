<?php

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\UnitsConverter;

class CSSOpacityChannel extends ColorChannel
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
        return $this->value;
    }

    public function get8Bit() : int
    {
        return UnitsConverter::float2IntEightBit($this->value);
    }

    public function get7Bit() : int
    {
        return UnitsConverter::float2IntSevenBit($this->value);
    }

    public function getPercent() : float
    {
        return UnitsConverter::float2percent($this->value);
    }

    public function invert() : CSSOpacityChannel
    {
        return ColorChannel::CSSOpacity(1-$this->value);
    }
}
