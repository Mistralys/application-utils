<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder;

use AppUtils\StyleCollection\StyleBuilder;

abstract class NumericContainer extends StyleContainer
{
    public function px(int $px, bool $important=false) : StyleBuilder
    {
        return $this->stylePX($px, $important);
    }

    public function percent(float $percent, bool $important=false) : StyleBuilder
    {
        return $this->stylePercent($percent, $important);
    }

    public function em(float $percent, bool $important=false) : StyleBuilder
    {
        return $this->styleEM($percent, $important);
    }
}
