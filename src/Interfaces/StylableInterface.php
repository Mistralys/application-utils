<?php
/**
 * File containing the interface {@see \AppUtils\Interfaces\StylableInterface}.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Interfaces\StylableInterface
 */

declare(strict_types=1);

namespace AppUtils\Interfaces;

use AppUtils\Interface_Stringable;
use AppUtils\NumberInfo;
use AppUtils\StyleCollection;
use AppUtils\Traits\StylableTrait;

/**
 * Interface for the Stylable trait.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see StylableTrait
 */
interface StylableInterface
{
    public function getStyles() : StyleCollection;

    public function hasStyles() : bool;

    /**
     * @param string $name
     * @param string $value
     * @param bool $important
     * @return $this
     */
    public function style(string $name, string $value, bool $important) : self;

    /**
     * @param string $name
     * @param string|number|NumberInfo|Interface_Stringable|NULL $value
     * @param bool $important
     * @return $this
     */
    public function styleAuto(string $name, $value, bool $important) : self;

    /**
     * @param string $name
     * @return $this
     */
    public function removeStyle(string $name) : self;
}
