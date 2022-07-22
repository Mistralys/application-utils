<?php
/**
 * File containing the {@link Request_Param_Validator_Valueslist} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Validator_Valueslist
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
class Request_Param_Validator_Valueslist extends Request_Param_Validator
{
    public function getDefaultOptions() : array
    {
        return array(
            'values' => array()
        );
    }

    /**
     * @return array<mixed>|mixed|NULL
     */
    protected function _validate()
    {
        $allowed = $this->getArrayOption('values');
        
        // if we are validating a subvalue, it means we are 
        // validating a single value in the existing list.
        if($this->isSubvalue) 
        {
            if(in_array($this->value, $allowed, true)) {
                return $this->value;
            }
            
            return null;
        }
        
        if(!is_array($this->value)) {
            return array();
        }
        
        $keep = array();
        foreach($this->value as $item) {
            if(in_array($item, $allowed, true)) {
                $keep[] = $item;
            }
        }
        
        return $keep;
    }
}
