<?php
/**
 * File containing the {@link Request_Param_Validator_Regex} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Regex
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Validates a request value using a regex. Returns null if the value does not match.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Regex extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _validate()
    {
        if(!is_scalar($this->value)) {
            return null;
        }
        
        // the only scalar value we do not want to work with
        // is a boolan, which is converted to an integer when
        // converted to string, which in turn can be validated
        // with a regex.
        if(is_bool($this->value)) {
            return null;
        }
        
        $value = (string)$this->value;
        
        if(preg_match($this->getStringOption('regex'), $value)) {
            return $value;
        }
        
        return null;
    }
}
