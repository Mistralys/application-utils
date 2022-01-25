<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors\Font;

use AppUtils\StyleCollection\StyleBuilder;
use AppUtils\StyleCollection\StyleBuilder\StyleContainer;

class FontSize extends StyleContainer
{
    protected function getName() : string
    {
        return 'font-size';
    }

    public function relativeLarger(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('larger', $important);
    }

    public function relativeSmaller(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('smaller', $important);
    }

    public function custom(string $value, bool $important=false) : StyleBuilder
    {
        return $this->setStyle($value, $important);
    }

    public function percent(float $percent, bool $important=false) : StyleBuilder
    {
        return $this->setStylePercent($percent, $important);
    }

    public function px(int $pixels, bool $important=false) : StyleBuilder
    {
        return $this->setStylePX($pixels, $important);
    }

    public function em(float $em, bool $important=false) : StyleBuilder
    {
        return $this->setStyleEM($em, $important);
    }

    public function rem(float $rem, bool $important=false) : StyleBuilder
    {
        return $this->setStyleREM($rem, $important);
    }
}
