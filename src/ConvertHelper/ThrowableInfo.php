<?php

declare(strict_types=1);

namespace AppUtils;

class ConvertHelper_ThrowableInfo
{
    use Traits_Optionable;
    
    const FORMAT_HTML = 'html';
    
   /**
    * @var \Throwable
    */
    protected $exception;
    
   /**
    * @var ConvertHelper_ThrowableInfo_Call[]
    */
    protected $calls;
    
    protected $code;
    
    protected $callsCount = 0;
    
   /**
    * @var \Throwable
    */
    protected $previous;
    
    public function __construct(\Throwable $e)
    {
        $this->exception = $e;
        
        $code = $e->getCode();
        if(!empty($code)) {
            $this->code = $code;
        }
        
        $previous = $e->getPrevious();
        if($previous instanceof \Throwable) {
            $this->previous = $previous;
        }
    }

    public function getDefaultOptions()
    {
        return array(
            'folder-depth' => 2
        );
    }
    
    public function hasPrevious()
    {
        return $this->previous !== null;
    }
    
    public function getPrevious() : \Throwable
    {
        
    }
    
    public function hasCode() : bool
    {
        return $this->code !== null;
    }
    
   /**
    * Improved textonly exception trace.
    */
    public function toString() : string
    {
        $calls = $this->getCalls();
        
        $string = 'Exception';
        
        if($this->hasCode()) {
            $string .= ' #'.$this->code;
        }
        
        $string .= ': '.$this->exception->getMessage().PHP_EOL;
        
        foreach($calls as $call) 
        {
            $string .= $call->toString().PHP_EOL;
        }
        
        if($this->hasPrevious()) {
            
        }
        
        return $string;
    }

   /**
    * Sets the maximum folder depth to show in the 
    * file paths, to avoid them being too long.
    * 
    * @param int $depth
    * @return ConvertHelper_ThrowableInfo
    */
    public function setFolderDepth(int $depth) : ConvertHelper_ThrowableInfo
    {
        return $this->setOption('folder-depth', $depth);
    }
    
    public function getFolderDepth() : int
    {
        return $this->getOption('folder-depth');
    }
    
   /**
    * Retrieves all function calls that led to the error.
    * @return ConvertHelper_ThrowableInfo_Call[]
    */
    public function getCalls()
    {
        $this->parse();
        
        return $this->calls;
    }
    
    public function countCalls()
    {
        $this->parse();
        
        return $this->callsCount;
    }
    
    protected function parse() : void
    {
        if(isset($this->calls)) {
            return;
        }
        
        $this->calls = array();
        
        $trace = $this->exception->getTrace();
        
        // add the origin file as entry
        array_unshift($trace, array(
            'file' => $this->exception->getFile(),
            'line' => $this->exception->getLine()
        ));
        
        $idx = 1;
        
        foreach($trace as $entry)
        {
            $this->calls[] = new ConvertHelper_ThrowableInfo_Call($this, $idx, $entry);
            
            $idx++;
        }
        
        // we want the last function call first
        $this->calls = array_reverse($this->calls, false);
        
        $this->callsCount = count($this->calls);
    }
    
    public function __toString()
    {
        return $this->toString();
    }
}