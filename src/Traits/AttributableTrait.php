<?php
/**
 * File containing the trait {@see \AppUtils\Traits\AttributableTrait}.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Traits\AttributableTrait
 */

declare(strict_types=1);

namespace AppUtils\Traits;

use AppUtils\AttributeCollection;
use AppUtils\Interface_Stringable;
use AppUtils\Interfaces\AttributableInterface;
use AppUtils\StringBuilder_Interface;

/**
 * Trait for objects that allow setting attributes,
 * using an internal {@see AttributeCollection}.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see AttributableInterface
 */
trait AttributableTrait
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
    public function attr(string $name, $value) : self
    {
        $this->getAttributes()->attr($name, $value);
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function attrURL(string $name, string $value) : self
    {
        $this->getAttributes()->attrURL($name, $value);
        return $this;
    }

    /**
     * @param string $name
     * @param string|number|bool|Interface_Stringable|StringBuilder_Interface|NULL $value
     * @return $this
     */
    public function attrQuotes(string $name, $value) : self
    {
        $this->getAttributes()->attrQuotes($name, $value);
        return $this;
    }


    /**
     * @param string $name
     * @param bool $enabled
     * @return $this
     */
    public function prop(string $name, bool $enabled=true) : self
    {
        $this->getAttributes()->prop($name, $enabled);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeAttribute(string $name) : self
    {
        $this->getAttributes()->remove($name);
        return $this;
    }
}
