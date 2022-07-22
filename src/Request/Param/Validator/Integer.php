<?php
/**
 * File containing the {@link Request_Param_Validator_Integer} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Integer
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Validates an integer.
 * 
 * Note: returns null if the value is not an integer, since any other 
 * value would be a valid integer that may have meaning in the application.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Integer extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _validate() : ?int
    {
        if(ConvertHelper::isInteger($this->value)) {
            return (int)$this->value;
        }
        
        return null;
    }
}
