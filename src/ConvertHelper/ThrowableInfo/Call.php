<?php

declare(strict_types=1);

namespace AppUtils;

class ConvertHelper_ThrowableInfo_Call
{
   /**
    * @var ConvertHelper_ThrowableInfo
    */
    protected $info;
    
   /**
    * @var array
    */
    protected $trace;
    
   /**
    * @var VariableInfo[]
    */
    protected $args;
    
   /**
    * The source file, if any
    * @var string
    */
    protected $file = '';
    
    protected $class = '';
    
   /**
    * @var int
    */
    protected $position;
    
    public function __construct(ConvertHelper_ThrowableInfo $info, int $position, array $trace)
    {
        $this->info = $info;
        $this->trace = $trace;
        $this->position = $position;
    }
    
    public function getPosition() : int
    {
        return $this->position;
    }
    
    public function getLine()
    {
        return $this->trace['line'];
    }
    
    public function hasArguments() : bool
    {
        $this->parse();
        
        return !empty($this->args);
    }
    
   /**
    * @return VariableInfo[]
    */
    public function getArguments()
    {
        $this->parse();
        
        return $this->args;
    }
    
    public function hasFile() : bool
    {
        $this->parse();
        
        return $this->file !== '';
    }
    
    public function hasFunction() : bool
    {
        return isset($this->trace['function']);
    }
    
    public function getFunction() : string
    {
        if(isset($this->trace['function'])) {
            return $this->trace['function'];
        }
        
        return '';
    }
    
    public function getFilePath() : string
    {
        $this->parse();
        
        return $this->file;
    }
    
    public function getFileName() : string
    {
        $this->parse();
        
        if($this->hasFile()) {
            return basename($this->file);
        }
        
        return '';
    }
    
    public function getFileRelative() : string
    {
        $this->parse();
        
        if($this->hasFile()) {
            return FileHelper::relativizePathByDepth($this->file, $this->info->getFolderDepth());
        }
        
        return '';
    }
    
    public function hasClass() : bool
    {
        $this->parse();
        
        return $this->class !== '';
    }
    
    public function getClass() : string
    {
        return $this->class;
    }
    
    protected function parse()
    {
        if(isset($this->args)) {
            return;
        }
        
        if(isset($this->trace['file']))
        {
            $this->file = FileHelper::normalizePath($this->trace['file']);
        }
        
        if(isset($this->trace['class'])) 
        {
            $this->class = $this->trace['class'];
        }
     
        $this->args = array();
        
        if(isset($this->trace['args']) && !empty($this->trace['args']))
        {
            foreach($this->trace['args'] as $arg) 
            {
                $this->args[] = parseVariable($arg);
            }
        }
    }
    
    public function toString()
    {
        $tokens = array();
        
        $padLength = strlen((string)$this->info->countCalls());
        
        $tokens[] = '#'.sprintf('%0'.$padLength.'d', $this->getPosition()).' ';
        
        if($this->hasFile()) {
            $tokens[] = $this->getFileRelative().':'.$this->getLine();
        }
        
        if($this->hasClass()) {
            $tokens[] = $this->getClass().'::'.$this->getFunction().'('.$this->argumentsToString().')';
        } else if($this->hasFunction()) {
            $tokens[] = $this->getFunction().'('.$this->argumentsToString().')';
        }
        
        return implode(' ', $tokens);
    }
    
    public function argumentsToString()
    {
        $tokens = array();
        
        foreach($this->args as $arg) 
        {
            $tokens[] = $arg->toString();
        }
        
        return implode(', ', $tokens); 
    }
}
