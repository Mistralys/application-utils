<?php

declare(strict_types=1);

namespace AppUtils;

abstract class VariableInfo_Renderer
{
   /**
    * @var mixed
    */
    protected $value;
    
   /**
    * @var VariableInfo
    */
    protected $info;
    
   /**
    * @var string
    */
    protected $type;
    
    public function __construct(VariableInfo $info)
    {
        $this->info = $info;
        $this->type = $info->getType();
        
        $this->init();
    }
    
    abstract protected function init();

   /**
    * Renders the value to the target format.
    * 
    * @return mixed
    */
    public function render()
    {
        return $this->_render();
    }
    
    abstract protected function _render();

    protected function cutString(string $string) : string
    {
        $cutAt = $this->info->getIntOption('cut-length', 1000);
        
        return ConvertHelper::text_cut($string, $cutAt, ' [...]');
    }
}
