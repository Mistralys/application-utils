<?php

declare(strict_types=1);

namespace AppUtils;

abstract class VariableInfo_Renderer_String extends VariableInfo_Renderer
{
   /**
    * @var mixed
    */
    protected $value;
    
    protected function init()
    {
        $this->value = $this->info->getValue();
    }
}
