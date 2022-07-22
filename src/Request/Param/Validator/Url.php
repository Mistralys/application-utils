<?php
/**
 * File containing the {@link Request_Param_Validator_Url} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Url
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Validates the syntax of an URL, but not its actual validity. 
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Url extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _validate() : string
    {
        if(!is_string($this->value)) {
            return '';
        }
        
        $info = parseURL($this->value);
        if($info->isValid()) {
            return $this->value;
        }
        
        return '';
    }
}
