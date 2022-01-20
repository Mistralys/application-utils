<?php

declare(strict_types=1);

namespace AppUtils;

trait Traits_Stylable
{
    abstract public function getStyles() : StyleCollection;

    public function hasStyles() : bool
    {
        return $this->getStyles()->hasStyles();
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $important
     * @return $this
     */
    public function style(string $name, string $value, bool $important)
    {
        $this->getStyles()->style($name, $value, $important);
        return $this;
    }

    /**
     * @param string $name
     * @param string|number|NumberInfo|Interface_Stringable|NULL $value
     * @param bool $important
     * @return $this
     */
    public function styleAuto(string $name, $value, bool $important)
    {
        $this->getStyles()->styleAuto($name, $value, $important);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeStyle(string $name)
    {
        $this->getStyles()->remove($name);
        return $this;
    }
}
