<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_HTML_Array extends VariableInfo_Renderer_HTML
{
    protected function _render() : string
    {
        $json = $this->info->toString();
        $json = $this->cutString($json);
        $json = nl2br($json);
        
        return $json;
    }
}
