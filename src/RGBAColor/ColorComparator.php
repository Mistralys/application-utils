<?php
/**
 * File containing the class {@see ColorComparator}.
 *
 * @see ColorComparator
 *@subpackage RGBAColor
 * @package AppUtils
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor;

/**
 * The comparator is used to compare color instances. It can be
 * used statically, or via the color's methods {@see RGBAColor::matches()}
 * and {@see RGBAColor::matchesAlpha()}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ColorComparator
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
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    public static function colorsMatch(RGBAColor $sourceColor, RGBAColor $targetColor) : bool
    {
        $parts = array(RGBAColor::CHANNEL_RED, RGBAColor::CHANNEL_GREEN, RGBAColor::CHANNEL_BLUE);

        foreach($parts as $part)
        {
            if($sourceColor->getColor($part)->get8Bit() !== $targetColor->getColor($part)->get8Bit())
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
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_COLOR_COMPONENT
     */
    public static function colorsMatchAlpha(RGBAColor $sourceColor, RGBAColor $targetColor) : bool
    {
        return
            self::colorsMatch($sourceColor, $targetColor)
            &&
            $sourceColor->getAlpha()->get8Bit() === $targetColor->getAlpha()->get8Bit();
    }
}
