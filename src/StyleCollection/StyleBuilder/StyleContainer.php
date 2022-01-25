<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection\StyleBuilder;

use AppUtils\StyleCollection;
use AppUtils\StyleCollection\StyleBuilder;

abstract class StyleContainer
{
    /**
     * @var StyleBuilder
     */
    protected $styles;

    /**
     * @var StyleCollection
     */
    protected $collection;

    /**
     * @var string
     */
    protected $name;

    public function __construct(StyleBuilder $styles, StyleCollection $collection)
    {
        $this->styles = $styles;
        $this->collection = $collection;
        $this->name = $this->getName();
    }

    abstract protected function getName() : string;

    final protected function setStyle(string $value, bool $important) : StyleBuilder
    {
        $this->collection->style($this->name, $value, $important);
        return $this->styles;
    }

    final protected function setStylePX(int $value, bool $important) : StyleBuilder
    {
        $this->collection->stylePX($this->name, $value, $important);
        return $this->styles;
    }

    final protected function setStylePercent(float $value, bool $important) : StyleBuilder
    {
        $this->collection->stylePercent($this->name, $value, $important);
        return $this->styles;
    }

    final protected function setStyleEM(float $value, bool $important) : StyleBuilder
    {
        $this->collection->styleEM($this->name, $value, $important);
        return $this->styles;
    }

    final protected function setStyleREM(float $value, bool $important) : StyleBuilder
    {
        $this->collection->styleREM($this->name, $value, $important);
        return $this->styles;
    }
}
