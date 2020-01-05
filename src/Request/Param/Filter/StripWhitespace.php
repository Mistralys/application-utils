<?php
/**
 * File containing the {@link Request_Param_Filter_StripWhitespace} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Filter_StripWhitespace
 */

declare(strict_types=1);

namespace AppUtils;

class Request_Param_Filter_StripWhitespace extends Request_Param_Filter
{
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected function _filter()
    {
        return preg_replace('/\s/', '', $this->value);
    }
}
