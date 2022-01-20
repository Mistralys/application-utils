<?php

declare(strict_types=1);

namespace AppUtils\AttributeCollection;

use AppUtils\AttributeCollection;

class AttributesRenderer
{
    /**
     * @var AttributeCollection
     */
    private $collection;

    public function __construct(AttributeCollection $collection)
    {
        $this->collection = $collection;
    }

    public function render() : string
    {
        $list = array();

        $attributes = $this->compileAttributes();

        if(empty($attributes))
        {
            return '';
        }

        foreach($attributes as $name => $value)
        {
            if($value === '')
            {
                continue;
            }

            $list[] = $this->renderAttribute($name, $value);
        }

        return ' '.implode(' ', $list);
    }

    /**
     * Compiles all attributes, including the dynamic ones,
     * into an associative array with attribute name => value
     * pairs.
     *
     * @return array<string,string>
     */
    public function compileAttributes() : array
    {
        $attributes = $this->collection->getRawAttributes();

        if($this->collection->hasClasses())
        {
            $attributes['class'] = $this->collection->classesToString();
        }

        if($this->collection->hasStyles())
        {
            $attributes['style'] = $this->collection->getStyles()
                ->configureForInline()
                ->render();
        }

        return $attributes;
    }

    private function renderAttribute(string $name, string $value) : string
    {
        if($name === $value)
        {
            return $name;
        }

        return sprintf(
            '%s="%s"',
            $name,
            $value
        );
    }
}
