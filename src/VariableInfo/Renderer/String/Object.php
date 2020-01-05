<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_String_Object extends VariableInfo_Renderer_String
{
    protected function _render()
    {
        return get_class($this->value);
    }
}
