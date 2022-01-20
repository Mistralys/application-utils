<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder;

use AppUtils\RGBAColor;
use AppUtils\StyleCollection\StyleBuilder;

abstract class ColorContainer extends StyleContainer
{
    public function rgba(RGBAColor $color, bool $important=false) : StyleBuilder
    {
        return $this->style($color->toCSS(), $important);
    }

    public function hex(RGBAColor $color, bool $important=false) : StyleBuilder
    {
        return $this->style($color->toHEX(), $important);
    }
}
