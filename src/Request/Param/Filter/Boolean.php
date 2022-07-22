<?php
/**
 * File containing the {@link Request_Param_Filter_Boolean} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Filter_Boolean
 */

declare(strict_types=1);

namespace AppUtils;

class Request_Param_Filter_Boolean extends Request_Param_Filter
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _filter() : bool
    {
        return $this->value === 'yes' || $this->value === 'true' || $this->value === true;
    }
}
