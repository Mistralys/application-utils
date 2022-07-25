<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_String_Integer extends VariableInfo_Renderer_String
{
    protected function _render() : string
    {
        return (string)$this->value;
    }
}
