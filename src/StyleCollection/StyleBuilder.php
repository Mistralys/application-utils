<?php

declare(strict_types=1);

namespace AppUtils\StyleCollection;

use AppUtils\Interface_Stringable;
use AppUtils\StyleCollection;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Background;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Background\BackgroundColor;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Color;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Display;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Font;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Height;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Width;

class StyleBuilder implements Interface_Stringable
{
    /**
     * @var StyleCollection
     */
    private $collection;

    private function __construct(?StyleCollection $collection=null)
    {
        if($collection === null)
        {
            $collection = StyleCollection::create();
        }

        $this->collection = $collection;
    }

    public static function create(?StyleCollection $collection=null) : StyleBuilder
    {
        return new StyleBuilder($collection);
    }

    public function display() : Display
    {
        return new Display($this, $this->collection);
    }

    public function font() : Font
    {
        return new Font($this, $this->collection);
    }

    public function color() : Color
    {
        return new Color($this, $this->collection);
    }

    public function background() : Background
    {
        return new Background($this, $this->collection);
    }

    public function width() : Width
    {
        return new Width($this, $this->collection);
    }

    public function height() : Height
    {
        return new Height($this, $this->collection);
    }

    public function __toString()
    {
        return $this->collection->render();
    }

    public function getCollection() : StyleCollection
    {
        return $this->collection;
    }
}
