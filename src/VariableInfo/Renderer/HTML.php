<?php

declare(strict_types=1);

namespace AppUtils;

abstract class VariableInfo_Renderer_HTML extends VariableInfo_Renderer
{
    protected static $colors = array(
        self::TYPE_DOUBLE => 'ce0237',
        self::TYPE_INTEGER => 'ce0237',
        self::TYPE_ARRAY => '027ace',
        self::TYPE_OBJECT => 'cf5e20',
        self::TYPE_RESOURCE => '1c2eb1',
        self::TYPE_STRING => '1fa507',
        self::TYPE_BOOLEAN => '1c2eb1',
        self::TYPE_NULL => '1c2eb1',
        self::TYPE_UNKNOWN => 'cc0000',
        self::TYPE_CALLABLE => 'cf5e20'
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
