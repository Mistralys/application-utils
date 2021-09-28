<?php
/**
 * File containing the class {@see RGBAColor_Factory}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @see RGBAColor_Factory
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * The factory is a static class with methods to create color
 * instances from a variety of color formats, like HEX strings
 * or color arrays.
 *
 * Use the {@see RGBAColor_Factory::createAuto()} to auto-detect
 * the type of color information.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RGBAColor_Factory
{
    /**
     * @var RGBAColor_PresetsManager|null
     */
    private static $presets = null;

    /**
     * Retrieves the presets manager, which can be used to
     * add new color presets to use with the {@see RGBAColor_Presets} class.
     *
     * @return RGBAColor_PresetsManager
     */
    public static function getPresetsManager() : RGBAColor_PresetsManager
    {
        if(!isset(self::$presets))
        {
            self::$presets = new RGBAColor_PresetsManager();
        }

        return self::$presets;
    }

    /**
     * Automatically detects the subject value to create a color from.
     *
     * The following values are supported:
     *
     * - An existing `RGBAColor` instance (is returned as-is)
     * - A color array with color keys (`red`, `green`, `blue`, `alpha`)
     *
     * @param array<string,int>|RGBAColor|string|mixed $subject
     * @return RGBAColor
     * @throws RGBAColor_Exception
     */
    public static function createAuto($subject) : RGBAColor
    {
        if($subject instanceof RGBAColor)
        {
            return $subject;
        }

        if(is_array($subject))
        {
            if(RGBAColor_Converter::isColorArray($subject))
            {
                return self::createFromColor($subject);
            }

            return self::createFromIndexedColor($subject);
        }

        if(is_string($subject))
        {
            return self::createFromHEX($subject);
        }

        throw new RGBAColor_Exception(
            'Unknown color subject',
            sprintf(
                'The given parameter was not recognized as a valid color format. Type: [%s]',
                gettype($subject)
            ),
            RGBAColor::ERROR_UNKNOWN_COLOR_SUBJECT
        );
    }

    /**
     * Creates a color instance from a color array with
     * the channel keys (`red`, `green`, `blue`, `alpha`),
     * where each channel uses the 0-255 numeric range.
     *
     * The `alpha` channel is optional.
     *
     * Example:
     *
     * <pre>
     * $color = RGBAColor_Factory::createFromColor(array(
     *    'red' => 45,
     *    'green' => 121,
     *    'blue' => 147
     * ));
     * </pre>
     *
     * @param array<string,int|float> $color
     * @return RGBAColor
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     * @see RGBAColor::ERROR_INVALID_PERCENTAGE_VALUE
     */
    public static function createFromColor(array $color) : RGBAColor
    {
        if(!isset($color[RGBAColor::COMPONENT_ALPHA]))
        {
            $color[RGBAColor::COMPONENT_ALPHA] = 255;
        }

        return new RGBAColor(
            RGBAColor_Converter::int2percent(intval($color[RGBAColor::COMPONENT_RED])),
            RGBAColor_Converter::int2percent(intval($color[RGBAColor::COMPONENT_GREEN])),
            RGBAColor_Converter::int2percent(intval($color[RGBAColor::COMPONENT_BLUE])),
            RGBAColor_Converter::int2percent(intval($color[RGBAColor::COMPONENT_ALPHA]))
        );
    }

    /**
     * Creates a color instance from an index array with
     * color values, without color name keys.
     *
     * Examples:
     *
     * <pre>
     * $solidColor = RGBAColor_Factory::createFromIndexedColor(array(
     *     145, // red
     *     120, // green
     *     68 // blue
     * ));
     *
     * $alphaColor = RGBAColor_Factory::createFromIndexedColor(array(
     *     145, // red
     *     120, // green
     *     68, // blue
     *     200 // alpha
     * ));
     * </pre>
     *
     * @param int[] $color
     * @return RGBAColor
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_AMOUNT_COLOR_KEYS
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     * @see RGBAColor::ERROR_INVALID_PERCENTAGE_VALUE
     */
    public static function createFromIndexedColor(array $color) : RGBAColor
    {
        $values = array_values($color);
        $amountKeys = count($values);

        if($amountKeys === 3)
        {
            return self::createFromColor(array(
                RGBAColor::COMPONENT_RED => $color[0],
                RGBAColor::COMPONENT_GREEN => $color[1],
                RGBAColor::COMPONENT_BLUE => $color[2]
            ));
        }

        if($amountKeys === 4)
        {
            return self::createFromColor(array(
                RGBAColor::COMPONENT_RED => $color[0],
                RGBAColor::COMPONENT_GREEN => $color[1],
                RGBAColor::COMPONENT_BLUE => $color[2],
                RGBAColor::COMPONENT_ALPHA => $color[3]
            ));
        }

        throw new RGBAColor_Exception(
            'Invalid amount of color values.',
            sprintf(
                'The array had [%s] keys, 3 or 4 expected.',
                $amountKeys
            ),
            RGBAColor::ERROR_INVALID_AMOUNT_COLOR_KEYS
        );
    }

    /**
     * Creates an RGBA color instance from a HEX color value.
     *
     * @param string $hex Either a RRGGBB or RRGGBBAA hex string.
     * @return RGBAColor
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_HEX_LENGTH
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     * @see RGBAColor::ERROR_INVALID_PERCENTAGE_VALUE
     */
    public static function createFromHEX(string $hex) : RGBAColor
    {
        return self::createFromColor(RGBAColor_Converter::hex2color($hex));
    }

    public static function createFromPreset(string $presetName) : RGBAColor
    {
        return self::createFromColor(self::getPresetsManager()->getPreset($presetName));
    }
}
