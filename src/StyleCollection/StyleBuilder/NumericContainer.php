<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder;

use AppUtils\StyleCollection\StyleBuilder;

abstract class NumericContainer extends StyleContainer
{
    public function px(int $px, bool $important=false) : StyleBuilder
    {
        return $this->setStylePX($px, $important);
    }

    public function percent(float $percent, bool $important=false) : StyleBuilder
    {
        return $this->setStylePercent($percent, $important);
    }

    public function em(float $percent, bool $important=false) : StyleBuilder
    {
        return $this->setStyleEM($percent, $important);
    }
}
