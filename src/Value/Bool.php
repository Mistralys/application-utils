<?php
/**
 * File containing the {@see AppUtils\Value_Bool} class.
 *
 * @package Application Utils
 * @subpackage Values
 * @see AppUtils\Value_Bool
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Boolean value. Offers a call representation for a boolean value.
 *
 * @package Application Utils
 * @subpackage Values
 */
class Value_Bool extends Value
{
    protected bool $value = false;
    
    public function __construct(bool $value=false)
    {
        $this->value = $value;
    }
    
    public function set(bool $value) : Value_Bool
    {
        $this->value = $value;
        
        return $this;
    }
    
    public function get() : bool
    {
        return $this->value;
    }
    
    public function isTrue() : bool
    {
        return $this->value === true;
    }
    
    public function isFalse() : bool
    {
        return $this->value === false;
    }
}
