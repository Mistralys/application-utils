<?php

declare(strict_types=1);

namespace AppUtils;

trait Traits_Attributable
{
    abstract public function getAttributes() : AttributeCollection;

    public function hasAttributes() : bool
    {
        return $this->getAttributes()->hasAttributes();
    }

    /**
     * @param string $name
     * @param string|number|bool|Interface_Stringable|StringBuilder_Interface|NULL $value
     * @return $this
     */
    public function attr(string $name, $value)
    {
        $this->getAttributes()->attr($name, $value);
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function attrURL(string $name, string $value)
    {
        $this->getAttributes()->attrURL($name, $value);
        return $this;
    }

    /**
     * @param string $name
     * @param string|number|bool|Interface_Stringable|StringBuilder_Interface|NULL $value
     * @return $this
     */
    public function attrQuotes(string $name, $value)
    {
        $this->getAttributes()->attrQuotes($name, $value);
        return $this;
    }


    /**
     * @param string $name
     * @param bool $enabled
     * @return $this
     */
    public function prop(string $name, bool $enabled=true)
    {
        $this->getAttributes()->prop($name, $enabled);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeAttribute(string $name)
    {
        $this->getAttributes()->remove($name);
        return $this;
    }
}
