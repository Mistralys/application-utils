<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors;

use AppUtils\StyleCollection\StyleBuilder;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Font\FontFamily;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Font\FontSize;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Font\FontStyle;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Font\FontWeight;
use AppUtils\StyleCollection\StyleBuilder\StyleContainer;

class Font extends StyleContainer
{
    /**
     * @return string
     */
    public function getName() : string
    {
        return 'font';
    }

    public function style() : FontStyle
    {
        return new FontStyle($this->styles, $this->collection);
    }

    public function family() : FontFamily
    {
        return new FontFamily($this->styles, $this->collection);
    }

    public function weight() : FontWeight
    {
        return new FontWeight($this->styles,$this->collection);
    }

    public function size() : FontSize
    {
        return new FontSize($this->styles,$this->collection);
    }
}
