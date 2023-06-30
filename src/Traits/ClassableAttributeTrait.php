<?php
/**
 * File containing the trait {@see \AppUtils\Traits\ClassableAttributeTrait}.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Traits\ClassableAttributeTrait
 */

declare(strict_types=1);

namespace AppUtils\Traits;

use AppUtils\AttributeCollection;
use AppUtils\Interfaces\ClassableAttributeInterface;

/**
 * Trait for objects that implement the classable
 * interface using the `class` attribute of an
 * {@see AttributeCollection} instance.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ClassableAttributeInterface
 */
trait ClassableAttributeTrait
{
    abstract public function getAttributes() : AttributeCollection;

    /**
     * @param string $name
     * @return $this
     */
    public function addClass($name) : self // No type hint on purpose, see interface
    {
        $this->getAttributes()->addClass($name);
        return $this;
    }

    /**
     * @param string[] $names
     * @return $this
     */
    public function addClasses(array $names) : self
    {
        $this->getAttributes()->addClasses($names);
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasClass(string $name) : bool
    {
        return $this->getAttributes()->hasClass($name);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeClass(string $name) : self
    {
        $this->getAttributes()->removeClass($name);
        return $this;
    }

    /**
     * @return string[]
     */
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
