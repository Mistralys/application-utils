<?php
/**
 * File containing the {@see AppUtils\Value_Bool_True} class.
 *
 * @package Application Utils
 * @subpackage Values
 * @see AppUtils\Value_Bool_True
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Sticky true-based boolean value: starts out as false, 
 * and if it is set to true, cannot be set to false again 
 * afterwards.
 *
 * @package Application Utils
 * @subpackage Values
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Value_Bool_True extends Value_Bool
{
    public function set(bool $value) : Value_Bool
    {
        if($value === true)
        {
            parent::set($value);
        }
        
        return $this;
    }
}
