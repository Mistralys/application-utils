<?php
/**
 * File containing the {@link Request_Param_Filter_String} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Filter_String
 */

declare(strict_types=1);

namespace AppUtils;

class Request_Param_Filter_String extends Request_Param_Filter
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _filter()
    {
        if(!is_scalar($this->value)) {
            return '';
        }
        
        return (string)$this->value;
    }
}
