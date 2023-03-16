<?php
/**
 * @package Application Utils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\ColorChannel\AlphaChannel
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\UnitsConverter;

/**
 * Color channel with values from 0.0 to +1.0.
 *
 * Native value: {@see self::getAlpha()}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class AlphaChannel extends ColorChannel
{
    public const VALUE_MIN = 0.0;
    public const VALUE_MAX = 1.0;

    /**
     * @var float
     */
    private float $value;

    public function __construct(float $value)
    {
        if($value < self::VALUE_MIN) { $value = self::VALUE_MIN; }
        if($value > self::VALUE_MAX) { $value = self::VALUE_MAX; }

        $this->value = $value;
    }

    /**
     * @return float 0.0-1.0
     */
    public function getValue() : float
    {
        return $this->value;
    }

    public function getAlpha() : float
    {
        return $this->value;
    }

    public function get8Bit() : int
    {
        return UnitsConverter::alpha2IntEightBit($this->value);
    }

    public function get7Bit() : int
    {
        return UnitsConverter::alpha2IntSevenBit($this->value);
    }

    public function getPercent() : float
    {
        return UnitsConverter::alpha2percent($this->value);
    }

    public function invert() : AlphaChannel
    {
        return ColorChannel::alpha(1-$this->value);
    }
}
