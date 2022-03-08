<?php
/**
 * File containing the trait {@see \AppUtils\Traits\StylableTrait}.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Traits\StylableTrait
 */

declare(strict_types=1);

namespace AppUtils\Traits;

use AppUtils\Interface_Stringable;
use AppUtils\Interfaces\StylableInterface;
use AppUtils\NumberInfo;
use AppUtils\StyleCollection;

/**
 * Trait for objects that support setting CSS styles.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see StylableInterface
 */
trait StylableTrait
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
    public function style(string $name, string $value, bool $important) : self
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
    public function styleAuto(string $name, $value, bool $important) : self
    {
        $this->getStyles()->styleAuto($name, $value, $important);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeStyle(string $name) : self
    {
        $this->getStyles()->remove($name);
        return $this;
    }
}
