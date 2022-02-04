<?php

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor\ColorChannel\DecimalChannel;
use AppUtils\RGBAColor\ColorChannel\EightBitChannel;
use AppUtils\RGBAColor\ColorChannel\PercentChannel;
use AppUtils\RGBAColor\ColorChannel\SevenBitChannel;

abstract class ColorChannel
{
    abstract public function get8Bit() : int;

    abstract public function get7Bit() : int;

    abstract public function getDecimal() : float;

    abstract public function getPercent() : float;

    /**
     * @return ColorChannel
     */
    abstract public function invert();

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
