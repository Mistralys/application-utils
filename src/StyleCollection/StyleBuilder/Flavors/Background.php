<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors;

use AppUtils\StyleCollection\StyleBuilder\Flavors\Background\BackgroundColor;
use AppUtils\StyleCollection\StyleBuilder\StyleContainer;

class Background extends StyleContainer
{
    /**
     * @return string
     */
    public function getName() : string
    {
        return 'background';
    }

    public function color() : BackgroundColor
    {
        return new BackgroundColor($this->styles, $this->collection);
    }
}
