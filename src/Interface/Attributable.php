<?php

declare(strict_types=1);

namespace AppUtils;

interface Interface_Attributable
{
    public function getAttributes() : AttributeCollection;

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function attr(string $name, string $value);

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function attrURL(string $name, string $value);

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function attrQuotes(string $name, string $value);

    /**
     * @param string $name
     * @param bool $enabled
     * @return $this
     */
    public function prop(string $name, bool $enabled=true);

    /**
     * @param string $name
     * @return $this
     */
    public function removeAttribute(string $name);
}
