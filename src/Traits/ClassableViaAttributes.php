<?php

declare(strict_types=1);

namespace AppUtils;

trait Trait_ClassableViaAttributes
{
    abstract public function getAttributes() : AttributeCollection;

    public function addClass(string $name) : HTMLTag
    {
        $this->getAttributes()->addClass($name);
        return $this;
    }

    public function addClasses(array $names) : HTMLTag
    {
        $this->getAttributes()->addClasses($names);
        return $this;
    }

    public function hasClass(string $name) : bool
    {
        return $this->getAttributes()->hasClass($name);
    }

    public function removeClass(string $name) : HTMLTag
    {
        $this->getAttributes()->removeClass($name);
        return $this;
    }

    public function getClasses() : array
    {
        return $this->getAttributes()->getClasses();
    }

    public function classesToString() : string
    {
        return $this->getAttributes()->classesToString();
    }

    public function classesToAttribute() : string
    {
        return $this->getAttributes()->classesToAttribute();
    }

    public function hasClasses() : bool
    {
        return $this->getAttributes()->hasClasses();
    }
}
