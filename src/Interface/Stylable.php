<?php

declare(strict_types=1);

namespace AppUtils;

interface Interface_Stylable
{
    public function getStyles() : StyleCollection;

    public function hasStyles() : bool;

    /**
     * @param string $name
     * @param string $value
     * @param bool $important
     * @return $this
     */
    public function style(string $name, string $value, bool $important);

    /**
     * @param string $name
     * @param $value
     * @param bool $important
     * @return $this
     */
    public function styleAuto(string $name, $value, bool $important);

    /**
     * @param string $name
     * @return $this
     */
    public function removeStyle(string $name);
}
