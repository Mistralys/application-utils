<?php
/**
 * File containing the interface {@see \AppUtils\Interfaces\AttributableInterface}.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Interfaces\AttributableInterface
 */

declare(strict_types=1);

namespace AppUtils\Interfaces;

use AppUtils\AttributeCollection;
use AppUtils\Traits\AttributableTrait;

/**
 * Interface for the Attributable trait.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see AttributableTrait
 */
interface AttributableInterface
{
    public function getAttributes() : AttributeCollection;

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function attr(string $name, string $value) : self;

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function attrURL(string $name, string $value) : self;

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function attrQuotes(string $name, string $value) : self;

    /**
     * @param string $name
     * @param bool $enabled
     * @return $this
     */
    public function prop(string $name, bool $enabled=true) : self;

    /**
     * @param string $name
     * @return $this
     */
    public function removeAttribute(string $name) : self;
}
