<?php
/**
 * File containing the {@link Request_Param_Validator_Json} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Json
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Makes sure that the value is a JSON-encoded string.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Json extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array(
            'arrays' => true
        );
    }
    
    protected function _validate()
    {
        if(!is_string($this->value)) {
            return '';
        }
        
        $value = trim($this->value);
        
        if(empty($value)) {
            return '';
        }
        
        // strictly validate for objects?
        if($this->getBoolOption('arrays') === false)
        {
            if(is_object(json_decode($value))) {
                return $value;
            }
        }
        else
        {
            if(is_array(json_decode($value, true))) {
                return $value;
            }
        }
        
        return '';
    }
}
