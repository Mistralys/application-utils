<?php

declare(strict_types=1);

namespace AppUtils;

class ConvertHelper_ThrowableInfo_Call
{
    const TYPE_FUNCTION_CALL = 'function';
    const TYPE_METHOD_CALL = 'method';
    const TYPE_SCRIPT_START = 'start';
    
   /**
    * @var ConvertHelper_ThrowableInfo
    */
    protected $info;
    
   /**
    * @var VariableInfo[]
    */
    protected $args = array();
    
   /**
    * The source file, if any
    * @var string
    */
    protected $file = '';
    
   /**
    * @var string
    */
    protected $class = '';
    
   /**
    * @var integer
    */
    protected $line = 0;
    
   /**
    * @var int
    */
    protected $position = 1;
    
   /**
    * @var string
    */
    protected $function = '';
    
   /**
    * @var string
    */
    protected $type = self::TYPE_SCRIPT_START;

    /**
     * @param ConvertHelper_ThrowableInfo $info
     * @param array<string,mixed> $data
     */
    protected function __construct(ConvertHelper_ThrowableInfo $info, array $data)
    {
        $this->info = $info;
        
        if(isset($data['serialized'])) 
        {
            $this->parseSerialized($data['serialized']);
        }
        else
        {
            $this->parseTrace($data['trace']);
            $this->position = $data['position'];
        }
        
        if($this->hasClass()) 
        {
            $this->type = self::TYPE_METHOD_CALL;
        }
        else if($this->hasFunction()) 
        {
            $this->type = self::TYPE_FUNCTION_CALL;
        }
    }
    
   /**
    * 1-based position of the call in the calls list.
    * @return int
    */
    public function getPosition() : int
    {
        return $this->position;
    }
    
    public function getLine() : int
    {
        return $this->line;
    }
    
   /**
    * Whether the call had any arguments.
    * @return bool
    */
    public function hasArguments() : bool
    {
        return !empty($this->args);
    }
    
   /**
    * @return VariableInfo[]
    */
    public function getArguments()
    {
        return $this->args;
    }
    
    public function hasFile() : bool
    {
        return $this->file !== '';
    }
    
    public function hasFunction() : bool
    {
        return !empty($this->function);
    }
    
    public function getFunction() : string
    {
        return $this->function;
    }
    
    public function getFilePath() : string
    {
        return $this->file;
    }
    
    public function getFileName() : string
    {
        if($this->hasFile()) {
            return basename($this->file);
        }
        
        return '';
    }
    
    public function getFileRelative() : string
    {
        if($this->hasFile()) {
            return FileHelper::relativizePathByDepth($this->file, $this->info->getFolderDepth());
        }
        
        return '';
    }
    
    public function hasClass() : bool
    {
        return $this->class !== '';
    }
    
    public function getClass() : string
    {
        return $this->class;
    }

    /**
     * @param array<string,mixed> $data
     * @throws BaseException
     */
    protected function parseSerialized(array $data) : void
    {
        $this->type = $data['type'];
        $this->line = $data['line'];
        $this->function = $data['function'];
        $this->file = $data['file'];
        $this->class = $data['class'];
        $this->position = $data['position'];
        
        foreach($data['arguments'] as $arg)
        {
            $this->args[] = VariableInfo::fromSerialized($arg);
        }
    }

    /**
     * @param array<string,mixed> $trace
     */
    protected function parseTrace(array $trace) : void
    {
        if(isset($trace['line']))
        {
            $this->line = intval($trace['line']);
        }
        
        if(isset($trace['function'])) 
        {
            $this->function = $trace['function'];
        }
        
        if(isset($trace['file']))
        {
            $this->file = FileHelper::normalizePath($trace['file']);
        }
        
        if(isset($trace['class'])) 
        {
            $this->class = $trace['class'];
        }
     
        if(isset($trace['args']) && !empty($trace['args']))
        {
            foreach($trace['args'] as $arg) 
            {
                $this->args[] = parseVariable($arg);
            }
        }
    }
    
    public function toString() : string
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
    
    public function argumentsToString() : string
    {
        $tokens = array();
        
        foreach($this->args as $arg) 
        {
            $tokens[] = $arg->toString();
        }
        
        return implode(', ', $tokens); 
    }
    
   /**
    * Retrieves the type of call: typically a function
    * call, or a method call of an object. Note that the
    * first call in a script does not have either.
    * 
    * @return string
    * 
    * @see ConvertHelper_ThrowableInfo_Call::TYPE_FUNCTION_CALL
    * @see ConvertHelper_ThrowableInfo_Call::TYPE_METHOD_CALL
    * @see ConvertHelper_ThrowableInfo_Call::TYPE_SCRIPT_START
    * @see ConvertHelper_ThrowableInfo_Call::hasFunction()
    * @see ConvertHelper_ThrowableInfo_Call::hasClass()
    */
    public function getType() : string
    {
        return $this->type;
    }
     
   /**
    * Serializes the call to an array, with all
    * necessary information. Can be used to restore
    * the call later using {@link ConvertHelper_ThrowableInfo_Call::fromSerialized()}.
    * 
    * @return array<string,mixed>
    */
    public function serialize() : array
    {
        $result = array(
            'type' => $this->getType(),
            'class' => $this->getClass(),
            'file' => $this->getFilePath(),
            'function' => $this->getFunction(),
            'line' => $this->getLine(),
            'position' => $this->getPosition(),
            'arguments' => array()
        );
        
        foreach($this->args as $argument)
        {
            $result['arguments'][] = $argument->serialize();
        }
        
        return $result;
    }

    /**
     * @param ConvertHelper_ThrowableInfo $info
     * @param int $position
     * @param array<string,mixed> $trace
     * @return ConvertHelper_ThrowableInfo_Call
     */
    public static function fromTrace(ConvertHelper_ThrowableInfo $info, int $position, array $trace) : ConvertHelper_ThrowableInfo_Call
    {
        return new ConvertHelper_ThrowableInfo_Call(
            $info, 
            array(
                'position' => $position,
                'trace' => $trace
            )
        );
    }

    /**
     * @param ConvertHelper_ThrowableInfo $info
     * @param array<string,mixed> $serialized
     * @return ConvertHelper_ThrowableInfo_Call
     */
    public static function fromSerialized(ConvertHelper_ThrowableInfo $info, array $serialized) : ConvertHelper_ThrowableInfo_Call
    {
        return new ConvertHelper_ThrowableInfo_Call(
            $info,
            array(
                'serialized' => $serialized
            )
        );
    }
}
