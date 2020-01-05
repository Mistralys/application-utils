<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_String_Double extends VariableInfo_Renderer_String
{
    protected function _render()
    {
        return (string)$this->value;
    }
}
