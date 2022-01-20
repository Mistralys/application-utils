<?php
/**
 * File containing the class {@see ColorPresets}.
 *
 * @see ColorPresets
 *@subpackage RGBAColor
 * @package AppUtils
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor;

/**
 * Quick access to the global color presets, as well as those that
 * were added via the manager.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ColorPresets
{
    public static function white() : RGBAColor
    {
        return ColorFactory::createFromPreset(PresetsManager::COLOR_WHITE);
    }

    public static function black() : RGBAColor
    {
        return ColorFactory::createFromPreset(PresetsManager::COLOR_BLACK);
    }

    public static function transparent() : RGBAColor
    {
        return ColorFactory::createFromPreset(PresetsManager::COLOR_TRANSPARENT);
    }

    public static function custom(string $presetName) : RGBAColor
    {
        return ColorFactory::createFromPreset($presetName);
    }
}
