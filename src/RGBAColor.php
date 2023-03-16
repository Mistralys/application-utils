<?php
/**
 * File containing the class {@see RGBAColor}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @see RGBAColor
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\RGBAColor\ArrayConverter;
use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\ColorChannel\BrightnessChannel;
use AppUtils\RGBAColor\ColorComparator;
use AppUtils\RGBAColor\ColorException;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\RGBAColor\FormatsConverter;
use ArrayAccess;

/**
 * Container for RGB color information, with optional alpha channel.
 * Allows treating the objects as an array, as a drop-in replacement
 * for the GD color functions.
 *
 * It can be cast to string, which returns the human-readable version
 * of the color as returned by {@see RGBAColor::getLabel()}.
 *
 * To create an instance, the easiest way is to use the {@see ColorFactory},
 * which offers different data models to get the color information
 * from.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @implements ArrayAccess<string,ColorChannel>
 */
class RGBAColor implements ArrayAccess, Interface_Stringable
{
    public const ERROR_UNKNOWN_COLOR_SUBJECT = 93401;
    public const ERROR_INVALID_COLOR_COMPONENT = 93402;
    public const ERROR_INVALID_PERCENTAGE_VALUE = 93503;
    public const ERROR_INVALID_HEX_LENGTH = 93505;
    public const ERROR_UNKNOWN_COLOR_PRESET = 93507;
    public const ERROR_INVALID_COLOR_ARRAY = 93508;

    public const CHANNEL_RED = 'red';
    public const CHANNEL_GREEN = 'green';
    public const CHANNEL_BLUE = 'blue';
    public const CHANNEL_ALPHA = 'alpha';

    /**
     * @var array<string,ColorChannel>
     */
    private array $color;

    /**
     * @var string[]
     */
    public const COLOR_COMPONENTS = array(
        self::CHANNEL_RED,
        self::CHANNEL_GREEN,
        self::CHANNEL_BLUE,
        self::CHANNEL_ALPHA
    );

    private string $name;

    /**
     * @param ColorChannel $red
     * @param ColorChannel $green
     * @param ColorChannel $blue
     * @param ColorChannel $alpha
     * @param string $name
     */
    public function __construct(ColorChannel $red, ColorChannel $green, ColorChannel $blue, ColorChannel $alpha, string $name)
    {
        $this->color[self::CHANNEL_RED] = $red;
        $this->color[self::CHANNEL_GREEN] = $green;
        $this->color[self::CHANNEL_BLUE] = $blue;
        $this->color[self::CHANNEL_ALPHA] = $alpha;
        $this->name = $name;
    }

    /**
     * Retrieves the color's name, if any. Colors created from
     * presets for example, inherit the name from the preset.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Human-readable label of the color. Automatically
     * switches between RGBA and RGB depending on whether
     * the color has any transparency.
     *
     * @return string
     */
    public function getLabel() : string
    {
        return FormatsConverter::color2readable($this);
    }

    // region: Get components

    /**
     * Gets the color's luminance equivalent.
     *
     * @return int Integer, from 0 to 255 (0=black, 255=white)
     * @see self::getBrightness()
     */
    public function getLuma() : int
    {
        return $this->toHSV()->getBrightness()->get8Bit();
    }

    /**
     * Retrieves the brightness of the color, in percent.
     * @return BrightnessChannel
     */
    public function getBrightness() : BrightnessChannel
    {
        return $this->toHSV()->getBrightness();
    }

    /**
     * Whether the alpha channel has a transparency value.
     * @return bool
     */
    public function hasTransparency() : bool
    {
        return $this->getAlpha()->get8Bit() > 0;
    }

    /**
     * The amount of red in the color.
     *
     * @return ColorChannel
     */
    public function getRed() : ColorChannel
    {
        return $this->color[self::CHANNEL_RED];
    }

    /**
     * The amount of green in the color.
     *
     * @return ColorChannel
     */
    public function getGreen() : ColorChannel
    {
        return $this->color[self::CHANNEL_GREEN];
    }

    /**
     * The amount of blue in the color.
     *
     * @return ColorChannel
     */
    public function getBlue() : ColorChannel
    {
        return $this->color[self::CHANNEL_BLUE];
    }

    /**
     * The opacity of the color (smaller value = opaque, higher value = transparent).
     *
     * @return ColorChannel
     */
    public function getAlpha() : ColorChannel
    {
        return $this->color[self::CHANNEL_ALPHA];
    }

    /**
     * Retrieves the current transparency value as a percentage.
     * 100 = fully transparent, 0 = fully opaque
     *
     * @return ColorChannel
     * @throws ColorException
     */
    public function getTransparency() : ColorChannel
    {
        return $this->color[self::CHANNEL_ALPHA]->invert();
    }

    /**
     * @param string $name
     * @return ColorChannel
     *
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    public function getColor(string $name) : ColorChannel
    {
        $this->requireValidComponent($name);

        return $this->color[$name];
    }

    // endregion

    // region: Operations

    /**
     * @param float $percent -100 to 100
     * @return RGBAColor (New instance)
     */
    public function adjustBrightness(float $percent) : RGBAColor
    {
        return $this
            ->toHSV()
            ->adjustBrightness($percent)
            ->toRGB();
    }

    // endregion

    // region: Converting

    /**
     * Converts the color to a HEX color value. This is either
     * a RRGGBB or RRGGBBAA string, depending on whether there
     * is an alpha channel value.
     *
     * @return string
     */
    public function toHEX() : string
    {
        return FormatsConverter::color2HEX($this);
    }

    public function toCSS() : string
    {
        return FormatsConverter::color2CSS($this);
    }

    /**
     * Converts the color to a Hue/Saturation/Brightness value.
     * Practical for adjusting the values, which is difficult
     * with pure RGB values.
     *
     * @return HSVColor
     */
    public function toHSV() : HSVColor
    {
        $hsv = FormatsConverter::rgb2hsv(
            $this->getRed()->get8Bit(),
            $this->getGreen()->get8Bit(),
            $this->getBlue()->get8Bit()
        );

        return new HSVColor(
            ColorChannel::hue($hsv['hue']),
            ColorChannel::saturation($hsv['saturation']),
            ColorChannel::brightness($hsv['brightness']),
            $this->getAlpha()
        );
    }

    /**
     * Converts the color to a color array.
     *
     * @return ArrayConverter
     */
    public function toArray() : ArrayConverter
    {
        return FormatsConverter::color2array($this);
    }

    // endregion

    // region: Setting color values

    /**
     * Returns a new instance with the modified color channel,
     * keeping all other color values.
     *
     * @param ColorChannel $red
     * @return RGBAColor
     */
    public function setRed(ColorChannel $red) : RGBAColor
    {
        return ColorFactory::create(
            $red,
            $this->getGreen(),
            $this->getBlue(),
            $this->getAlpha()
        );
    }

    /**
     * Returns a new instance with the modified color channel,
     * keeping all other color values.
     *
     * @param ColorChannel $green
     * @return RGBAColor
     */
    public function setGreen(ColorChannel $green) : RGBAColor
    {
        return ColorFactory::create(
            $this->getRed(),
            $green,
            $this->getBlue(),
            $this->getAlpha()
        );
    }

    /**
     * Returns a new instance with the modified color channel,
     * keeping all other color values.
     *
     * @param ColorChannel $blue
     * @return RGBAColor
     */
    public function setBlue(ColorChannel $blue) : RGBAColor
    {
        return ColorFactory::create(
            $this->getRed(),
            $this->getGreen(),
            $blue,
            $this->getAlpha()
        );
    }

    /**
     * Returns a new instance with the modified color channel,
     * keeping all other color values.
     *
     * @param ColorChannel $alpha
     * @return RGBAColor
     */
    public function setAlpha(ColorChannel $alpha) : RGBAColor
    {
        return ColorFactory::create(
            $this->getRed(),
            $this->getGreen(),
            $this->getBlue(),
            $alpha
        );
    }

    /**
     * Sets the transparency of the color, which is an alias
     * for the alpha, but inverted. Returns a new color
     * instance with the modified value.
     *
     * @param ColorChannel $transparency
     * @return RGBAColor
     */
    public function setTransparency(ColorChannel $transparency) : RGBAColor
    {
        return $this->setAlpha($transparency->invert());
    }

    /**
     * Changes the color's brightness to the specified level.
     *
     * @param int|float $brightness 0 to 100
     * @return RGBAColor
     */
    public function setBrightness($brightness) : RGBAColor
    {
        return $this
            ->toHSV()
            ->setBrightness($brightness)
            ->toRGB();
    }

    /**
     * Sets the color, and returns a new RGBAColor instance
     * with the target color modified.
     *
     * @param string $name
     * @param ColorChannel $value
     * @return RGBAColor
     *
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    public function setColor(string $name, ColorChannel $value) : RGBAColor
    {
        $this->requireValidComponent($name);

        $channels = array(
            self::CHANNEL_RED => $this->getRed(),
            self::CHANNEL_GREEN => $this->getGreen(),
            self::CHANNEL_BLUE => $this->getBlue(),
            self::CHANNEL_ALPHA => $this->getAlpha()
        );

        $channels[$name] = $value;

        return ColorFactory::create(
            $channels[self::CHANNEL_RED],
            $channels[self::CHANNEL_GREEN],
            $channels[self::CHANNEL_BLUE],
            $channels[self::CHANNEL_ALPHA]
        );
    }

    // endregion

    /**
     * @param string $name
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    private function requireValidComponent(string $name) : void
    {
        if(in_array($name, self::COLOR_COMPONENTS))
        {
            return;
        }

        throw new ColorException(
            'Invalid color component.',
            sprintf(
                'The color component [%s] is not a valid color component. Valid components are: [%s].',
                $name,
                implode(', ', self::COLOR_COMPONENTS)
            ),
            self::ERROR_INVALID_COLOR_COMPONENT
        );
    }

    /**
     * Whether this color is the same as the specified color.
     *
     * NOTE: Only compares the RGB color values, ignoring the
     * transparency. To also compare transparency, use `matchesAlpha()`.
     *
     * @param RGBAColor $targetColor
     * @return bool
     * @throws ColorException
     */
    public function matches(RGBAColor $targetColor) : bool
    {
        return ColorComparator::colorsMatch($this, $targetColor);
    }

    /**
     * Whether this color is the same as the specified color,
     * including the alpha channel.
     *
     * @param RGBAColor $targetColor
     * @return bool
     * @throws ColorException
     */
    public function matchesAlpha(RGBAColor $targetColor) : bool
    {
        return ColorComparator::colorsMatchAlpha($this, $targetColor);
    }

    public function __toString()
    {
        return $this->getLabel();
    }

    // region: ArrayAccess interface methods

    public function offsetExists($offset)
    {
        $key = (string)$offset;

        return isset($this->color[$key]);
    }

    public function offsetGet($offset)
    {
        $key = (string)$offset;

        return $this->color[$key] ?? 0;
    }

    public function offsetSet($offset, $value)
    {
        $this->setColor((string)$offset, $value);
    }

    public function offsetUnset($offset)
    {

    }

    // endregion
}
