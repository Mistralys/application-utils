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

    public function block(bool $important=false) : StyleBuilder
    {
        return $this->style('block', $important);
    }

    public function contents(bool $important=false) : StyleBuilder
    {
        return $this->style('contents', $important);
    }

    public function grid(bool $important=false) : StyleBuilder
    {
        return $this->style('grid', $important);
    }

    public function inline(bool $important=false) : StyleBuilder
    {
        return $this->style('inline', $important);
    }

    public function inlineFlex(bool $important=false) : StyleBuilder
    {
        return $this->style('inline-flex', $important);
    }

    public function inlineGrid(bool $important=false) : StyleBuilder
    {
        return $this->style('inline-grid', $important);
    }

    public function inlineTable(bool $important=false) : StyleBuilder
    {
        return $this->style('inline-table', $important);
    }

    public function inlineBlock(bool $important=false) : StyleBuilder
    {
        return $this->style('inline-block', $important);
    }
}
