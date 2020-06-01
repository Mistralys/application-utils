<?php
/**
 * File containing the {@see AppUtils\Value_Bool_False} class.
 *
 * @package Application Utils
 * @subpackage Values
 * @see AppUtils\Value_Bool_False
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Sticky false-based boolean value: starts out as true,
 * and if it is set to false, cannot be set to true again
 * afterwards.
 *
 * @package Application Utils
 * @subpackage Values
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Value_Bool_False extends Value_Bool
{
    public function __construct(bool $value=true)
    {
        parent::__construct($value);
    }
    
    public function set(bool $value) : Value_Bool
    {
        if($value === false)
        {
            parent::set($value);
        }
        
        return $this;
    }
}
