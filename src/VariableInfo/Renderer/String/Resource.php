<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_String_Resource extends VariableInfo_Renderer_String
{
    protected function _render() : string
    {
        $string = (string)$this->value;
        $string = substr($string, strpos($string, '#'));
        
        return $string;
    }
}
