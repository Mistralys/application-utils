<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_String_Array extends VariableInfo_Renderer_String
{
    protected function _render()
    {
        $result = array();
        
        foreach($this->value as $key => $val)
        {
            $result[$key] = parseVariable($val)->toString();
        }
        
        return print_r($result, true);
    }
}
