<?php

declare(strict_types=1);

namespace AppUtils;

abstract class VariableInfo_Renderer_String extends VariableInfo_Renderer
{
    protected function init() : void
    {
        $this->value = $this->info->getValue();
    }
}
