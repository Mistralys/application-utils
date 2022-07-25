<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_HTML_Integer extends VariableInfo_Renderer_HTML
{
    protected function _render() : string
    {
        return $this->info->toString();
    }
}
