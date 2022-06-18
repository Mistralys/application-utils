<?php
/**
 * File containing the class {@see ColorFactory}.
 *
 * @see ColorFactory
 *@subpackage RGBAColor
 * @package AppUtils
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorPresets\CannedColors;

/**
 * The factory is a static class with methods to create color
 * instances from a variety of color formats, like HEX strings
 * or color arrays.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ColorFactory
{
    /**
     * @var PresetsManager|null
     */
    private static ?PresetsManager $presets = null;

    /**
     * Retrieves the presets manager, which can be used to
     * add new color presets to use with the {@see ColorPresets} class.
     *
     * @return PresetsManager
     */
    public static function getPresetsManager() : PresetsManager
    {
        if(!isset(self::$presets))
        {
            self::$presets = new PresetsManager();
        }

        return self::$presets;
    }

    /**
     * @param ColorChannel $red
     * @param ColorChannel $green
     * @param ColorChannel $blue
     * @param ColorChannel|null $opacity
     * @param string $name
     * @return RGBAColor
     */
    public static function create(ColorChannel $red, ColorChannel $green, ColorChannel $blue, ?ColorChannel $opacity=null, string $name='') : RGBAColor
    {
        if($opacity === null)
        {
            $opacity = ColorChannel::eightBit(255);
        }

        return new RGBAColor($red, $green, $blue, $opacity, $name);
    }

    /**
     * Attempts to automatically detect the type of color
     * information provided, and returns a color instance
     * if possible.
     *
     * This allows passing the following values:
     *
     * - A HEX color string
     * - An 8-Bit color array
     * - An existing RGBAColor instance
     * - A preset name
     * - An empty or NULL value
     *
     * @param string|array<int|string,int|float>|RGBAColor|NULL $subject
     * @return RGBAColor|NULL
     * @throws ColorException
     */
    public static function createAuto($subject) : ?RGBAColor
    {
        if($subject instanceof RGBAColor)
        {
            return $subject;
        }

        if(is_array($subject))
        {
            return self::createFrom8BitArray($subject);
        }

        $hexOrPreset = (string)$subject;

        if($hexOrPreset === '')
        {
            return null;
        }

        $manager = self::getPresetsManager();

        if($manager->hasPreset($hexOrPreset))
        {
            return $manager->getPreset($hexOrPreset);
        }

        if(preg_match('/[a-f0-9]{3,8}/i', $hexOrPreset))
        {
            return self::createFromHEX($hexOrPreset);
        }

        return null;
    }

    /**
     * Creates a color instance from a color array with
     * the channel keys (`red`, `green`, `blue`, `alpha`),
     * where each channel uses the 0-255 numeric range.
     *
     * The `alpha` channel is optional.
     *
     * With an associative color array:
     *
     * <pre>
     * $color = RGBAColor_Factory::createFromColor(array(
     *    'red' => 45,
     *    'green' => 121,
     *    'blue' => 147,
     *    ['alpha' => 255]
     * ));
     * </pre>
     *
     * With an indexed color array:
     *
     * <pre>
     * $color = RGBAColor_Factory::createFromColor(array(
     *    45,
     *    121,
     *    147,
     *    [255]
     * ));
     * </pre>
     *
     * @param array<string|int,int> $color
     * @return RGBAColor
     *
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     * @see RGBAColor::ERROR_INVALID_PERCENTAGE_VALUE
     */
    public static function createFrom8BitArray(array $color) : RGBAColor
    {
        $color = FormatsConverter::array2associative($color);

        if(!isset($color[RGBAColor::CHANNEL_ALPHA]))
        {
            $color[RGBAColor::CHANNEL_ALPHA] = 255;
        }

        return self::create(
            ColorChannel::eightBit((int)$color[RGBAColor::CHANNEL_RED]),
            ColorChannel::eightBit((int)$color[RGBAColor::CHANNEL_GREEN]),
            ColorChannel::eightBit((int)$color[RGBAColor::CHANNEL_BLUE]),
            ColorChannel::eightBit((int)$color[RGBAColor::CHANNEL_ALPHA])
        );
    }

    /**
     * Creates an RGBA color instance from a HEX color value.
     *
     * @param string $hex Either a RRGGBB or RRGGBBAA hex string.
     * @param string $name
     * @return RGBAColor
     *
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_HEX_LENGTH
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     * @see RGBAColor::ERROR_INVALID_PERCENTAGE_VALUE
     */
    public static function createFromHEX(string $hex, string $name='') : RGBAColor
    {
        return FormatsConverter::hex2color($hex, $name);
    }

    public static function createFromPreset(string $presetName) : RGBAColor
    {
        return self::getPresetsManager()->getPreset($presetName);
    }

    public static function preset() : CannedColors
    {
        return new CannedColors();
    }

    /**
     * Creates a color from percentage-based values for all channels.
     *
     * @param float $red 0-100
     * @param float $green 0-100
     * @param float $blue 0-100
     * @param float $opacity 0-100
     * @param string $name
     * @return RGBAColor
     */
    public static function createPercent(float $red, float $green, float $blue, float $opacity=100, string $name='') : RGBAColor
    {
        return new RGBAColor(
            ColorChannel::percent($red),
            ColorChannel::percent($green),
            ColorChannel::percent($blue),
            ColorChannel::percent($opacity),
            $name
        );
    }

    /**
     * Creates a color instance from CSS color values,
     * where the opacity is a 1-based value.
     *
     * @param int $red 0-255
     * @param int $green 0-255
     * @param int $blue 0-255
     * @param float $opacity 0-1
     * @param string $name
     * @return RGBAColor
     */
    public static function createCSS(int $red, int $green, int $blue, float $opacity=1, string $name='') : RGBAColor
    {
        return self::create(
            ColorChannel::eightBit($red),
            ColorChannel::eightBit($green),
            ColorChannel::eightBit($blue),
            ColorChannel::decimal($opacity),
            $name
        );
    }

    /**
     * Creates a color instance from 255-based values
     * for all channels.
     *
     * @param int $red 0-255
     * @param int $green 0-255
     * @param int $blue 0-255
     * @param int $opacity 0-255
     * @param string $name
     * @return RGBAColor
     */
    public static function create8Bit(int $red, int $green, int $blue, int $opacity=255, string $name='') : RGBAColor
    {
        return self::create(
            ColorChannel::eightBit($red),
            ColorChannel::eightBit($green),
            ColorChannel::eightBit($blue),
            ColorChannel::eightBit($opacity),
            $name
        );
    }

    /**
     * Creates a color from a GD library compatible set
     * of color values: 8-Bit values for the color components,
     * and 7-Bit for the alpha channel.
     *
     * @param int $red 0-255
     * @param int $green 0-255
     * @param int $blue 0-255
     * @param int $opacity 0-127
     * @param string $name
     * @return RGBAColor
     */
    public static function createGD(int $red, int $green, int $blue, int $opacity=127, string $name='') : RGBAColor
    {
        return self::create(
            ColorChannel::eightBit($red),
            ColorChannel::eightBit($green),
            ColorChannel::eightBit($blue),
            ColorChannel::sevenBit($opacity),
            $name
        );
    }
}
