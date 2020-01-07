<?php

declare(strict_types=1);

namespace AppUtils;

abstract class VariableInfo_Renderer_HTML extends VariableInfo_Renderer
{
    protected static $colors = array(
        VariableInfo::TYPE_DOUBLE => 'ce0237',
        VariableInfo::TYPE_INTEGER => 'ce0237',
        VariableInfo::TYPE_ARRAY => '027ace',
        VariableInfo::TYPE_OBJECT => 'cf5e20',
        VariableInfo::TYPE_RESOURCE => '1c2eb1',
        VariableInfo::TYPE_STRING => '1fa507',
        VariableInfo::TYPE_BOOLEAN => '1c2eb1',
        VariableInfo::TYPE_NULL => '1c2eb1',
        VariableInfo::TYPE_UNKNOWN => 'cc0000',
        VariableInfo::TYPE_CALLABLE => 'cf5e20'
    );
    
    protected function init()
    {
        
    }
    
    public function getTypeColor() : string
    {
        return self::$colors[$this->type];
    }
    
    public function render()
    {
        $converted = sprintf(
            '<span style="color:#%1$s" class="variable-value-%3$s">'.
                '%2$s'.
            '</span>',
            $this->getTypeColor(),
            $this->_render(),
            str_replace(' ', '-', $this->type)
        );
        
        if($this->info->getBoolOption('prepend-type') && !$this->info->isNull())
        {
            $typeLabel = '<span style="color:#1c2eb1" class="variable-type">'.$this->info->getType().'</span> ';
            $converted = $typeLabel.' '.$converted;
        }
        
        return $converted;
    }
}
