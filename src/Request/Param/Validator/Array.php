<?php
/**
 * File containing the {@link Request_Param_Validator_Array} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Array
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Makes sure that the value is an array.
 *
 * @package Application Utils
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Request_Param_Validator_Array extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array();
    }

    /**
     * @return mixed|NULL
     */
    protected function _validate()
    {
        // subvalues cannot be arrays
        if($this->isSubvalue) {
            return $this->value;
        }
        
        if(is_array($this->value)) {
            return $this->value;
        }
        
        return null;
    }
}
