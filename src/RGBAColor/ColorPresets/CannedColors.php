<?php

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorPresets;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\RGBAColor\PresetsManager;

class CannedColors
{
    /**
     * @var PresetsManager
     */
    private $manager;

    public function __construct()
    {
        $this->manager = ColorFactory::getPresetsManager();
    }

    public function white() : RGBAColor
    {
        return $this->manager->getPreset(PresetsManager::COLOR_WHITE);
    }

    public function black() : RGBAColor
    {
        return $this->manager->getPreset(PresetsManager::COLOR_BLACK);
    }

    public function transparent() : RGBAColor
    {
        return $this->manager->getPreset(PresetsManager::COLOR_TRANSPARENT);
    }
}
