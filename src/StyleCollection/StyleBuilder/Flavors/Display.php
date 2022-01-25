<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder\Flavors;

use AppUtils\StyleCollection\StyleBuilder;
use AppUtils\StyleCollection\StyleBuilder\StyleContainer;

class Display extends StyleContainer
{
    /**
     * @return string
     */
    public function getName() : string
    {
        return 'display';
    }

    /**
     * Sets a custom display value, e.g. "flex", "list-item"...
     *
     * @param string $value
     * @param bool $important
     * @return StyleBuilder
     */
    public function custom(string $value, bool $important=false) : StyleBuilder
    {
        return $this->setStyle($value, $important);
    }

    public function block(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('block', $important);
    }

    public function none(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('none', $important);
    }

    public function inline(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('inline', $important);
    }

    public function inlineBlock(bool $important=false) : StyleBuilder
    {
        return $this->setStyle('inline-block', $important);
    }
}
