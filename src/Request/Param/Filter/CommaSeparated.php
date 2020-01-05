<?php
/**
 * File containing the {@link Request_Param_Filter_CommaSeparated} class.
 *
 * @package Application Utils
 * @subpackage Request
 * @see Request_Param_Filter_CommaSeparated
 */

declare(strict_types=1);

namespace AppUtils;

class Request_Param_Filter_CommaSeparated extends Request_Param_Filter
{
    public function getDefaultOptions() : array
    {
        return array(
            'trimEntries' => true,
            'stripEmptyEntries' => true
        );
    }
    
    protected function _filter()
    {
        if(is_array($this->value)) {
            return $this->value;
        }
        
        if($this->value === '' || $this->value === null || !is_string($this->value)) {
            return array();
        }
        
        $trimEntries = $this->getBoolOption('trimEntries');
        $stripEmptyEntries = $this->getBoolOption('stripEmptyEntries');
        $result = explode(',', $this->value);
        
        if(!$trimEntries && !$stripEmptyEntries) {
            return $result;
        }
        
        $keep = array();
        
        foreach($result as $entry)
        {
            if($trimEntries === true) {
                $entry = trim($entry);
            }
            
            if($stripEmptyEntries === true && $entry === '') {
                continue;
            }
            
            $keep[] = $entry;
        }
        
        return $keep;
    }
}
