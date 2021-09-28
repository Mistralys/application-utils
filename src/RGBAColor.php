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

use ArrayAccess;

/**
 * Container for RGB color information, with optional alpha channel.
 * Allows treating the objects as an array, as a drop-in replacement
 * for the GD color functions.
 *
 * It can be cast to string, which returns the human-readable version
 * of the color as returned by {@see RGBAColor::getLabel()}.
 *
 * To create an instance, the easiest way is to use the {@see RGBAColor_Factory},
 * which offers different data models to get the color information
 * from.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @implements ArrayAccess<string,float>
 */
class RGBAColor implements ArrayAccess, Interface_Stringable
{
    const ERROR_UNKNOWN_COLOR_SUBJECT = 93401;
    const ERROR_INVALID_COLOR_COMPONENT = 93402;
    const ERROR_INVALID_PERCENTAGE_VALUE = 93503;
    const ERROR_INVALID_COLOR_VALUE = 93504;
    const ERROR_INVALID_HEX_LENGTH = 93505;
    const ERROR_INVALID_AMOUNT_COLOR_KEYS = 93506;
    const ERROR_UNKNOWN_COLOR_PRESET = 93507;
    const ERROR_INVALID_COLOR_ARRAY = 93508;

    const COMPONENT_RED = 'red';
    const COMPONENT_GREEN = 'green';
    const COMPONENT_BLUE = 'blue';
    const COMPONENT_ALPHA = 'alpha';

    /**
     * @var array<string,float>
     */
    private $color;

    /**
     * @var string[]
     */
    const COLOR_COMPONENTS = array(
        self::COMPONENT_RED,
        self::COMPONENT_GREEN,
        self::COMPONENT_BLUE,
        self::COMPONENT_ALPHA
    );

    /**
     * @param float $red
     * @param float $green
     * @param float $blue
     * @param float $alpha
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     * @see RGBAColor::ERROR_INVALID_PERCENTAGE_VALUE
     */
    public function __construct(float $red, float $green, float $blue, float $alpha=100)
    {
        $this->setColorPercentage(self::COMPONENT_RED, $red);
        $this->setColorPercentage(self:: COMPONENT_GREEN, $green);
        $this->setColorPercentage(self::COMPONENT_BLUE, $blue);
        $this->setColorPercentage(self::COMPONENT_ALPHA, $alpha);
    }

    /**
     * Whether the alpha channel has a transparency value.
     * @return bool
     */
    public function hasTransparency() : bool
    {
        return $this->getAlpha() < 255;
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
        return RGBAColor_Converter::color2readable($this);
    }

    /**
     * The amount of green in the color (0-255).
     * @return int
     * @throws RGBAColor_Exception
     */
    public function getRed() : int
    {
        return $this->getColorValue(self::COMPONENT_RED);
    }

    /**
     * The amount of green in the color (0-255).
     * @return int
     * @throws RGBAColor_Exception
     */
    public function getGreen() : int
    {
        return $this->getColorValue(self::COMPONENT_GREEN);
    }

    /**
     * The amount of blue in the color (0-255).
     * @return int
     * @throws RGBAColor_Exception
     */
    public function getBlue() : int
    {
        return $this->getColorValue(self::COMPONENT_BLUE);
    }

    /**
     * The opacity of the color, from 0 (full transparency) to 255 (no transparency).
     * @return int
     * @throws RGBAColor_Exception
     */
    public function getAlpha() : int
    {
        return $this->getColorValue(self::COMPONENT_ALPHA);
    }

    /**
     * Sets the alpha channel value.
     *
     * @param int $alpha 0-255
     * @return $this
     * @throws RGBAColor_Exception
     */
    public function setAlpha(int $alpha) : RGBAColor
    {
        return $this->setColorValue(self::COMPONENT_ALPHA, $alpha);
    }

    /**
     * Sets the alpha channel of the color using
     * a percent-based transparency value.
     *
     * @param float $transparency The transparency as a percentage (0 = opaque, 100 = transparent)
     * @return RGBAColor
     * @throws RGBAColor_Exception
     */
    public function setTransparency(float $transparency) : RGBAColor
    {
        return $this->setColorPercentage(self::COMPONENT_ALPHA, 100-$transparency);
    }

    /**
     * Sets the alpha channel of the color using
     * a percent-based opacity value.
     *
     * @param float $opacity 0 = transparent, 100 = opaque
     * @return RGBAColor
     * @throws RGBAColor_Exception
     */
    public function setOpacity(float $opacity) : RGBAColor
    {
        return $this->setColorPercentage(self::COMPONENT_ALPHA, $opacity);
    }

    /**
     * Retrieves the current transparency value as a percentage.
     * 0 = opaque, 100 = fully transparent
     *
     * @return float
     * @throws RGBAColor_Exception
     */
    public function getTransparency() : float
    {
        return 100 - $this->getColorPercentage(self::COMPONENT_ALPHA);
    }

    /**
     * Retrieves the current opacity value as a percentage.
     * 0 = transparent, 100 = fully opaque
     *
     * @return float
     * @throws RGBAColor_Exception
     */
    public function getOpacity() : float
    {
        return $this->getColorPercentage(self::COMPONENT_ALPHA);
    }

    /**
     * Retrieves the color value as a percentage of the
     * color value (ranged from 0 to 255).
     *
     * @param string $name
     * @return float 0-100
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    public function getColorPercentage(string $name) : float
    {
        $this->requireValidComponent($name);

        return $this->color[$name];
    }

    /**
     * @param string $name
     * @return int
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    public function getColorValue(string $name) : int
    {
        $percent = $this->getColorPercentage($name);

        return RGBAColor_Converter::percent2int($percent);
    }

    /**
     * Converts the color to a HEX color value. This is either
     * a RRGGBB or RRGGBBAA string, depending on whether there
     * is an alpha channel value.
     *
     * @return string
     * @throws RGBAColor_Exception
     */
    public function toHEX() : string
    {
        return RGBAColor_Converter::color2HEX($this);
    }

    /**
     * Converts the color to a color array.
     *
     * @return array{red:int,green:int,blue:int,alpha:int}
     * @throws RGBAColor_Exception
     */
    public function toArray() : array
    {
        return array(
            self::COMPONENT_RED => $this->getRed(),
            self::COMPONENT_GREEN => $this->getBlue(),
            self::COMPONENT_BLUE => $this->getBlue(),
            self::COMPONENT_ALPHA => $this->getAlpha()
        );
    }

    /**
     * Sets a color value by a percentage.
     *
     * NOTE: Since the value needs to be converted to an
     * integer, using `getColorPercentage()` does not
     * necessarily return the same value as the one used with
     * `setColorPercentage()`.
     *
     * @param string $name
     * @param float $percentage The color value as a percentage (0-100%)
     * @return $this
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     * @see RGBAColor::ERROR_INVALID_PERCENTAGE_VALUE
     */
    public function setColorPercentage(string $name, float $percentage) : RGBAColor
    {
        $this->requireValidComponent($name);

        if($percentage >= 0 && $percentage <= 100)
        {
            $this->color[$name] = $percentage;
            return $this;
        }

        throw new RGBAColor_Exception(
            'Invalid percentage value.',
            sprintf(
                'The value [%s] is not a valid percentage number (0-100) for color [%s].',
                $percentage,
                $name
            ),
            self::ERROR_INVALID_PERCENTAGE_VALUE
        );
    }

    /**
     * @param string $name
     * @param int $value Color value from 0-255
     * @return $this
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     * @see RGBAColor::ERROR_INVALID_COLOR_VALUE
     */
    public function setColorValue(string $name, int $value) : RGBAColor
    {
        $this->requireValidComponent($name);

        if($value >= 0 && $value <= 255)
        {
            // Convert the value to a percentage for the internal storage.
            $percentage = $value * 100 / 255;
            return $this->setColorPercentage($name, $percentage);
        }

        throw new RGBAColor_Exception(
            'Invalid color value.',
            sprintf(
                'The color value [%s] for [%s] is invalid. Valid range is 0-255.',
                $value,
                $name
            ),
            self::ERROR_INVALID_COLOR_VALUE
        );
    }

    /**
     * @param string $name
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    private function requireValidComponent(string $name) : void
    {
        if(in_array($name, self::COLOR_COMPONENTS))
        {
            return;
        }

        throw new RGBAColor_Exception(
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
     * @throws RGBAColor_Exception
     */
    public function matches(RGBAColor $targetColor) : bool
    {
        return RGBAColor_Comparator::colorsMatch($this, $targetColor);
    }

    /**
     * Whether this color is the same as the specified color,
     * including the alpha channel.
     *
     * @param RGBAColor $targetColor
     * @return bool
     * @throws RGBAColor_Exception
     */
    public function matchesAlpha(RGBAColor $targetColor) : bool
    {
        return RGBAColor_Comparator::colorsMatchAlpha($this, $targetColor);
    }

    public function __toString()
    {
        return $this->getLabel();
    }

    // region: ArrayAccess interface methods

    public function offsetExists($offset)
    {
        $key = strval($offset);

        return isset($this->color[$key]);
    }

    public function offsetGet($offset)
    {
        $key = strval($offset);

        if(isset($this->color[$key]))
        {
            return $this->color[$key];
        }

        return 0;
    }

    public function offsetSet($offset, $value)
    {
        $this->setColorValue(strval($offset), intval($value));
    }

    public function offsetUnset($offset)
    {

    }

    // endregion
}
