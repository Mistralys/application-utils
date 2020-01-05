<?php
/**
 * File containing the {@link Request_Param_Validator_Numeric} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Numeric
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Validates a numeric value: returns null if the value is not in numeric notation.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Numeric extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _validate()
    {
        if(is_numeric($this->value)) {
            return $this->value * 1;
        }
        
        return null;
    }
}
