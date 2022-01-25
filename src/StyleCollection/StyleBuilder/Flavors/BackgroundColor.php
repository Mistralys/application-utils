<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors;

use AppUtils\StyleCollection\StyleBuilder\ColorContainer;

class BackgroundColor extends ColorContainer
{
    /**
     * @return string
     */
    public function getName() : string
    {
        return 'background-color';
    }
}
