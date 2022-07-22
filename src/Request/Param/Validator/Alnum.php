<?php
/**
 * File containing the {@link Request_Param_Validator_Alnum} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Alnum
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Validates a string containing only letters, lowercase and uppercase, and numbers.
 * 
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Alnum extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _validate() : ?string
    {
        $value = (string)$this->value;

        if (preg_match('/\A[a-zA-Z0-9]+\z/', $value)) {
            return $value;
        }
        
        return null;
    }
}
