<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_String_Unknowntype extends VariableInfo_Renderer_String
{
    protected function _render()
    {
        return 'unknown type';
    }
}
