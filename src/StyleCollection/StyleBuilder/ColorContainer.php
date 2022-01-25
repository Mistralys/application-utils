<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorException;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\StyleCollection\StyleBuilder;

abstract class ColorContainer extends StyleContainer
{
    public function rgbaValues(int $red, int $green, int $blue, float $opacity=1) : StyleBuilder
    {
        return $this->rgba(ColorFactory::createCSS($red, $green, $blue, $opacity));
    }

    public function rgba(RGBAColor $color, bool $important=false) : StyleBuilder
    {
        return $this->style($color->toCSS(), $important);
    }

    public function hex(RGBAColor $color, bool $important=false) : StyleBuilder
    {
        return $this->style('#'.$color->toHEX(), $important);
    }

    /**
     * Uses a HEX color value string.
     *
     * @param string $hex The color, e.g. "fff", "FAFAFA", or with alpha channel "CCCCCCAA"
     * @param bool $important
     * @return StyleBuilder
     * @throws ColorException
     */
    public function hexString(string $hex, bool $important=false) : StyleBuilder
    {
        return $this->hex(ColorFactory::createFromHEX($hex), $important);
    }
}
