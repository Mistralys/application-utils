<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors;

use AppUtils\StyleCollection\StyleBuilder\ColorContainer;

class Color extends ColorContainer
{
    /**
     * @return string
     */
    public function getName() : string
    {
        return 'color';
    }
}
