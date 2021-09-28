<?php
/**
 * File containing the class {@see RGBAColor_Presets}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @see RGBAColor_Presets
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Quick access to the global color presets, as well as those that
 * were added via the manager.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RGBAColor_Presets
{
    public static function white() : RGBAColor
    {
        return RGBAColor_Factory::createFromPreset(RGBAColor_PresetsManager::COLOR_WHITE);
    }

    public static function black() : RGBAColor
    {
        return RGBAColor_Factory::createFromPreset(RGBAColor_PresetsManager::COLOR_BLACK);
    }

    public static function transparent() : RGBAColor
    {
        return RGBAColor_Factory::createFromPreset(RGBAColor_PresetsManager::COLOR_TRANSPARENT);
    }

    public static function custom(string $presetName) : RGBAColor
    {
        return RGBAColor_Factory::createFromPreset($presetName);
    }
}
