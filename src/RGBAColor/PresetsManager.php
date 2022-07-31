<?php
/**
 * File containing the class {@see PresetsManager}.
 *
 * @see PresetsManager
 *@subpackage RGBAColor
 * @package AppUtils
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor;

/**
 * The presets manager allows adding more presets that can
 * then be used with the {@see ColorPresets} class to
 * create color instances.
 *
 * To get the manager instance, use the factory's method:
 * {@see ColorFactory::getPresetsManager()}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://en.wikipedia.org/wiki/List_of_colors_(compact)
 */
class PresetsManager
{
    public const ERROR_CANNOT_OVERWRITE_BUILT_IN_PRESET = 94001;

    public const COLOR_WHITE = 'white';
    public const COLOR_BLACK = 'black';
    public const COLOR_TRANSPARENT = 'transparent';

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
     * @throws ColorException
     */
    private function init() : void
    {
        if(self::$initialized === true)
        {
            return;
        }

        $this
            ->registerGlobalPreset(self::COLOR_WHITE, 255, 255, 255, 0)
            ->registerGlobalPreset(self::COLOR_BLACK, 0, 0, 0, 0)
            ->registerGlobalPreset(self::COLOR_TRANSPARENT, 0, 0, 0, 255);
    }

    public function hasPreset(string $name) : bool
    {
        return isset($this->customPresets[$name]) || isset(self::$globalPresets[$name]);
    }

    /**
     * @param string $name
     * @return RGBAColor
     *
     * @throws ColorException
     * @see RGBAColor::ERROR_UNKNOWN_COLOR_PRESET
     */
    public function getPreset(string $name) : RGBAColor
    {
        $preset = null;

        if(isset($this->customPresets[$name]))
        {
            $preset = $this->customPresets[$name];
        }
        else if(isset(self::$globalPresets[$name]))
        {
            $preset = self::$globalPresets[$name];
        }

        if($preset !== null)
        {
            return ColorFactory::create(
                ColorChannel::eightBit($preset[RGBAColor::CHANNEL_RED]),
                ColorChannel::eightBit($preset[RGBAColor::CHANNEL_GREEN]),
                ColorChannel::eightBit($preset[RGBAColor::CHANNEL_BLUE]),
                ColorChannel::eightBit($preset[RGBAColor::CHANNEL_ALPHA]),
                $name
            );
        }

        throw new ColorException(
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
     * @throws ColorException
     */
    private function registerGlobalPreset(string $name, int $red, int $green, int $blue, int $alpha) : PresetsManager
    {
        $this->requireNotGlobal($name);

        if(!isset(self::$globalPresets[$name]))
        {
            self::$globalPresets[$name] = array(
                RGBAColor::CHANNEL_RED => $red,
                RGBAColor::CHANNEL_GREEN => $green,
                RGBAColor::CHANNEL_BLUE=> $blue,
                RGBAColor::CHANNEL_ALPHA => $alpha
            );

            return $this;
        }

        throw new ColorException(
            'Cannot replace global color preset',
            sprintf(
            'The built-in global presets like [%s] may not be overwritten. Prefer adding a regular preset instead.',
                $name
            ),
            self::ERROR_CANNOT_OVERWRITE_BUILT_IN_PRESET
        );
    }

    public function registerPreset(string $name, int $red, int $green, int $blue, int $alpha=255) : PresetsManager
    {
        $this->requireNotGlobal($name);

        $this->customPresets[$name] = array(
            RGBAColor::CHANNEL_RED => $red,
            RGBAColor::CHANNEL_GREEN => $green,
            RGBAColor::CHANNEL_BLUE=> $blue,
            RGBAColor::CHANNEL_ALPHA => $alpha
        );

        return $this;
    }

    private function requireNotGlobal(string $name) : void
    {
        if(!isset(self::$globalPresets[$name]))
        {
            return;
        }

        throw new ColorException(
            'Cannot replace global color preset',
            sprintf(
                'The built-in global presets like [%s] may not be overwritten.',
                $name
            ),
            self::ERROR_CANNOT_OVERWRITE_BUILT_IN_PRESET
        );
    }
}

