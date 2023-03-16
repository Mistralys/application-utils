<?php
/**
 * @package Application Utils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\ColorChannel\HueChannel
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\UnitsConverter;
use testsuites\Traits\RenderableTests;

/**
 * Color channel with values from 0 to 360,
 * intended to represent the hue of a color.
 *
 * Native value: {@see self::getValue()}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HueChannel extends ColorChannel
{
    public const VALUE_MIN = 0.0;
    public const VALUE_MAX = 360.0;

    /**
     * @var float
     */
    private float $value;

    /**
     * @param int|float $value
     */
    public function __construct($value)
    {
        $value = (float)$value;

        if($value < self::VALUE_MIN) { $value = self::VALUE_MIN; }
        if($value > self::VALUE_MAX) { $value = self::VALUE_MAX; }

        $this->value = $value;
    }

    /**
     * Retrieves the float value with full precision.
     * @return float 0 to 360
     * @see self::getValueRounded()
     */
    public function getValue() : float
    {
        return $this->value;
    }

    /**
     * Retrieves the value, rounded and converted to an integer.
     * @return int
     */
    public function getValueRounded() : int
    {
        return (int)round($this->getValue());
    }

    public function get8Bit() : int
    {
        return UnitsConverter::hue2IntEightBit($this->value);
    }

    public function get7Bit() : int
    {
        return UnitsConverter::hue2IntSevenBit($this->value);
    }

    public function getAlpha() : float
    {
        return UnitsConverter::hue2Alpha($this->value);
    }

    public function getPercent() : float
    {
        return UnitsConverter::hue2Percent($this->value);
    }

    public function invert() : HueChannel
    {
        return ColorChannel::hue(360-$this->value);
    }
}
