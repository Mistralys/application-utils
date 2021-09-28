<?php
/**
 * File containing the class {@see RGBAColor_PresetsManager}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @see RGBAColor_PresetsManager
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * The presets manager allows adding more presets that can
 * then be used with the {@see RGBAColor_Presets} class to
 * create color instances.
 *
 * To get the manager instance, use the factory's method:
 * {@see RGBAColor_Factory::getPresetsManager()}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://en.wikipedia.org/wiki/List_of_colors_(compact)
 */
class RGBAColor_PresetsManager
{
    const ERROR_CANNOT_OVERWRITE_BUILT_IN_PRESET = 94001;

    const COLOR_WHITE = 'white';
    const COLOR_BLACK = 'black';
    const COLOR_TRANSPARENT = 'transparent';

    /**
     * @var array<string,array<string,int>>
     */
    private static $globalPresets = array();

    /**
     * @var bool
     */
    private static $initialized = false;

    /**
     * @var array<string,array<string,int>>
     */
    private $customPresets = array();

    public function __construct()
    {
        $this->init();
    }

    /**
     * Registers the global color presets.
     *
     * @throws RGBAColor_Exception
     */
    private function init() : void
    {
        if(self::$initialized === true)
        {
            return;
        }

        $this
            ->registerGlobalPreset(self::COLOR_WHITE, 255, 255, 255, 255)
            ->registerGlobalPreset(self::COLOR_BLACK, 0,0,0, 255)
            ->registerGlobalPreset(self::COLOR_TRANSPARENT, 0, 0, 0, 0);
    }

    /**
     * @param string $name
     * @return int[]
     *
     * @throws RGBAColor_Exception
     * @see RGBAColor::ERROR_UNKNOWN_COLOR_PRESET
     */
    public function getPreset(string $name) : array
    {
        if(isset($this->customPresets[$name]))
        {
            return $this->customPresets[$name];
        }

        if(isset(self::$globalPresets[$name]))
        {
            return self::$globalPresets[$name];
        }

        throw new RGBAColor_Exception(
            'No such color preset.',
            sprintf(
                'The color preset [%s] has not been registered, either as global or custom preset.',
                $name
            ),
            RGBAColor::ERROR_UNKNOWN_COLOR_PRESET
        );
    }

    /**
     * @param string $name
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     * @return $this
     * @throws RGBAColor_Exception
     */
    private function registerGlobalPreset(string $name, int $red, int $green, int $blue, int $alpha) : RGBAColor_PresetsManager
    {
        $this->requireNotGlobal($name);

        if(!isset(self::$globalPresets[$name]))
        {
            self::$globalPresets[$name] = array(
                RGBAColor::COMPONENT_RED => $red,
                RGBAColor::COMPONENT_GREEN => $green,
                RGBAColor::COMPONENT_BLUE=> $blue,
                RGBAColor::COMPONENT_ALPHA => $alpha
            );

            return $this;
        }

        throw new RGBAColor_Exception(
            'Cannot replace global color preset',
            sprintf(
            'The built-in global presets like [%s] may not be overwritten. Prefer adding a regular preset instead.',
                $name
            ),
            self::ERROR_CANNOT_OVERWRITE_BUILT_IN_PRESET
        );
    }

    public function registerPreset(string $name, int $red, int $green, int $blue, int $alpha=255) : RGBAColor_PresetsManager
    {
        $this->requireNotGlobal($name);

        $this->customPresets[$name] = array(
            RGBAColor::COMPONENT_RED => $red,
            RGBAColor::COMPONENT_GREEN => $green,
            RGBAColor::COMPONENT_BLUE=> $blue,
            RGBAColor::COMPONENT_ALPHA => $alpha
        );

        return $this;
    }

    private function requireNotGlobal(string $name) : void
    {
        if(!isset(self::$globalPresets[$name]))
        {
            return;
        }

        throw new RGBAColor_Exception(
            'Cannot replace global color preset',
            sprintf(
                'The built-in global presets like [%s] may not be overwritten.',
                $name
            ),
            self::ERROR_CANNOT_OVERWRITE_BUILT_IN_PRESET
        );
    }
}

