<?php
/**
 * File containing the {@link Request_Param_Validator_Alpha} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Alpha
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Validates a string containing only letters, lowercase and uppercase.
 * 
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Alpha extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _validate() : ?string
    {
        if(!is_scalar($this->value)) {
            return null;
        }

        $value = (string)$this->value;
        
        if(preg_match('/\A[a-zA-Z]+\z/', $value)) {
            return $value;
        }
        
        return null;
    }
}
