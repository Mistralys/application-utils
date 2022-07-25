<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_String_Boolean extends VariableInfo_Renderer_String
{
    protected function _render() : string
    {
        return ConvertHelper::bool2string($this->value);
    }
}
