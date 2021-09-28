<?php
/**
 * File containing the class {@see RGBAColor_Comparator}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @see RGBAColor_Comparator
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * The comparator is used to compare color instances. It can be
 * used statically, or via the color's methods {@see RGBAColor::matches()}
 * and {@see RGBAColor::matchesAlpha()}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RGBAColor_Comparator
{
    /**
     * Whether this color is the same as the specified color.
     *
     * NOTE: Only compares the RGB color values, ignoring the
     * transparency. To also compare transparency, use `matchesAlpha()`.
     *
     * @param RGBAColor $sourceColor
     * @param RGBAColor $targetColor
     * @return bool
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    public static function colorsMatch(RGBAColor $sourceColor, RGBAColor $targetColor) : bool
    {
        $parts = array(RGBAColor::COMPONENT_RED, RGBAColor::COMPONENT_GREEN, RGBAColor::COMPONENT_BLUE);

        foreach($parts as $part)
        {
            if($sourceColor->getColorValue($part) !== $targetColor->getColorValue($part))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether this color is the same as the specified color,
     * including the alpha channel.
     *
     * @param RGBAColor $sourceColor
     * @param RGBAColor $targetColor
     * @return bool
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    public static function colorsMatchAlpha(RGBAColor $sourceColor, RGBAColor $targetColor) : bool
    {
        return
            self::colorsMatch($sourceColor, $targetColor)
            &&
            $sourceColor->getAlpha() === $targetColor->getAlpha();
    }
}
