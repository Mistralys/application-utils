<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors;

use AppUtils\StyleCollection\StyleBuilder;
use AppUtils\StyleCollection\StyleBuilder\NumericContainer;

class Width extends NumericContainer
{
    protected function getName() : string
    {
        return 'width';
    }

    public function auto(bool $important=false) : StyleBuilder
    {
        return $this->style('auto', $important);
    }
}
