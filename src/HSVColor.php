<?php

declare(strict_types=1);

namespace AppUtils;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\ColorChannel\AlphaChannel;
use AppUtils\RGBAColor\ColorChannel\HueChannel;
use AppUtils\RGBAColor\ColorChannel\BrightnessChannel;
use AppUtils\RGBAColor\ColorChannel\SaturationChannel;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\RGBAColor\FormatsConverter;
use ArrayAccess;

/**
 * Handles color values based on Hue, Saturation and Brightness.
 *
 * NOTE: The array access implementation allows accessing the
 * color values, but since they are immutable, changing or
 * removing them is ignored.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @implements ArrayAccess<string,float> Only for accessing values - changing them is ignored.
 */
class HSVColor implements ArrayAccess
{
    private HueChannel $hue;
    private SaturationChannel $saturation;
    private BrightnessChannel $brightness;
    private AlphaChannel $alpha;

    public function __construct(HueChannel $hue, SaturationChannel $saturation, BrightnessChannel $brightness, ?AlphaChannel $alpha=null)
    {
        if($alpha === null) {
            $alpha = ColorChannel::alpha(0);
        }

        $this->hue = $hue;
        $this->saturation = $saturation;
        $this->brightness = $brightness;
        $this->alpha = $alpha;
    }

    public function getHue() : HueChannel
    {
        return $this->hue;
    }

    public function getBrightness() : BrightnessChannel
    {
        return $this->brightness;
    }

    public function getSaturation() : SaturationChannel
    {
        return $this->saturation;
    }

    public function getAlpha() : AlphaChannel
    {
        return $this->alpha;
    }

    /**
     * Sets the color's brightness.
     *
     * @param int|float|BrightnessChannel $brightness 0-100
     * @return HSVColor (New instance)
     */
    public function setBrightness($brightness) : HSVColor
    {
        return new HSVColor(
            $this->getHue(),
            $this->getSaturation(),
            ColorChannel::brightness($brightness),
            $this->getAlpha()
        );
    }

    /**
     * Sets the color's hue.
     *
     * @param int|float|HueChannel $hue 0 to 360
     * @return HSVColor (New instance)
     */
    public function setHue($hue) : HSVColor
    {
        return new HSVColor(
            ColorChannel::hue($hue),
            $this->getSaturation(),
            $this->getBrightness(),
            $this->getAlpha()
        );
    }

    /**
     * Sets the color's saturation.
     *
     * @param int|float|SaturationChannel $saturation 0 to 100
     * @return HSVColor (New instance)
     */
    public function setSaturation($saturation) : HSVColor
    {
        return new HSVColor(
            $this->getHue(),
            ColorChannel::saturation($saturation),
            $this->getBrightness(),
            $this->getAlpha()
        );
    }


    /**
     * Sets the color's alpha channel.
     *
     * @param float|AlphaChannel $alpha 0.0 to 1.0
     * @return HSVColor (New instance)
     */
    public function setAlpha($alpha) : HSVColor
    {
        return new HSVColor(
            $this->getHue(),
            $this->getSaturation(),
            $this->getBrightness(),
            ColorChannel::alpha($alpha)
        );
    }

    // region: Conversion methods

    /**
     * Converts the color to an RGB value.
     *
     * @return RGBAColor
     */
    public function toRGB() : RGBAColor
    {
        $rgb = FormatsConverter::hsv2rgb(
            $this->getHue()->getValue(),
            $this->getSaturation()->getValue(),
            $this->getBrightness()->getValue()
        );

        return ColorFactory::create(
            ColorChannel::eightBit($rgb['red']),
            ColorChannel::eightBit($rgb['green']),
            ColorChannel::eightBit($rgb['blue']),
            $this->getAlpha()
        );
    }

    /**
     * @return array{hue:float,saturation:float,brightness:float,alpha:float}
     */
    public function toArray() : array
    {
        return array(
            'hue' => $this->getHue()->getValue(),
            'saturation' => $this->getSaturation()->getValue(),
            'brightness' => $this->getBrightness()->getValue(),
            'alpha' => $this->getAlpha()->getValue()
        );
    }

    // endregion

    // region: Operations

    /**
     * Adjusts the color's brightness by the specified percent,
     * based on the current value.
     *
     * NOTE: Keep in mind that increasing the brightness by 50%
     * on a very dark color will only increase it slightly (example:
     * 5% luminosity + 50% = 7.5%). In some cases, setting the
     * brightness directly will be more logical.
     *
     * Can be a negative value to reduce the brightness.
     *
     * @param float $percent -100 to 100
     * @return HSVColor (New instance)
     */
    public function adjustBrightness(float $percent) : HSVColor
    {
        $value = $this->getBrightness()->getValue();
        $value += $value * $percent / 100;

        return new HSVColor(
            $this->getHue(),
            $this->getSaturation(),
            ColorChannel::brightness($value),
            $this->getAlpha()
        );
    }

    // endregion

    // region: Array access

    /**
     * @param float $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        $array = $this->toArray();
        return isset($array[$offset]);
    }

    /**
     * @param string $offset
     * @return float
     */
    public function offsetGet($offset) : float
    {
        $array = $this->toArray();

        return $array[$offset] ?? 0.0;
    }

    /**
     * @param string $offset
     * @param int|float $value
     * @return void
     */
    public function offsetSet($offset, $value) : void
    {
        // ignored.
    }

    /**
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset) : void
    {
        // ignored.
    }

    // endregion
}
