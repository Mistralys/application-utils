<?php

declare(strict_types=1);

namespace AppUtils;

class VariableInfo_Renderer_HTML_String extends VariableInfo_Renderer_HTML
{
    protected function _render() : string
    {
        $string = $this->info->toString();
        $string = $this->cutString($string);
        $string = nl2br(htmlspecialchars($string));
        
        return '&quot;'.$string.'&quot;';
    }
}
