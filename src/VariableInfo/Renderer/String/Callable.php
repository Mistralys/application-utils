<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_String_Callable extends VariableInfo_Renderer_String
{
    protected function _render()
    {
        $string = '';
        
        if(is_string($this->value[0])) 
        {
            $string .= $this->value[0].'::';
        } 
        else 
        {
            $string .= get_class($this->value[0]).'->';
        }
        
        $string .= $this->value[1].'()';
        
        return $string;
    }
}
