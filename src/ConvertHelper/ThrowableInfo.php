<?php

declare(strict_types=1);

namespace AppUtils;

class ConvertHelper_ThrowableInfo implements Interface_Optionable
{
    use Traits_Optionable;
    
    const ERROR_NO_PREVIOUS_EXCEPTION = 43301;
    
    const FORMAT_HTML = 'html';
    const CONTEXT_COMMAND_LINE = 'cli';
    const CONTEXT_WEB = 'web';
    
   /**
    * @var \Throwable
    */
    protected $exception;
    
   /**
    * @var ConvertHelper_ThrowableInfo_Call[]
    */
    protected $calls = array();
    
   /**
    * @var integer
    */
    protected $code;
    
   /**
    * @var string
    */
    protected $message;
    
   /**
    * @var integer
    */
    protected $callsCount = 0;
    
   /**
    * @var ConvertHelper_ThrowableInfo
    */
    protected $previous;
    
   /**
    * @var string
    */
    protected $referer = '';
    
   /**
    * @var \DateTime
    */
    protected $date;
    
   /**
    * @var string
    */
    protected $context = self::CONTEXT_WEB;
    
    protected function __construct($subject)
    {
        if(is_array($subject))
        {
            $this->parseSerialized($subject);
        }
        else
        {
            $this->parseException($subject);
        }
    }
    
    public static function fromThrowable(\Throwable $e)
    {
        return new ConvertHelper_ThrowableInfo($e);
    }
    
    public static function fromSerialized(array $serialized)
    {
        return new ConvertHelper_ThrowableInfo($serialized);
    }
    
    public function getCode() : int
    {
        return $this->code;
    }
    
    public function getMessage() : string
    {
        return $this->message;
    }

    public function getDefaultOptions() : array
    {
        return array(
            'folder-depth' => 2
        );
    }
    
    public function hasPrevious() : bool
    {
        return isset($this->previous);
    }
    
   /**
    * Retrieves the information on the previous exception.
    * 
    * NOTE: Throws an exception if there is no previous 
    * exception. Use hasPrevious() first to avoid this.
    * 
    * @throws ConvertHelper_Exception
    * @return ConvertHelper_ThrowableInfo
    * @see ConvertHelper_ThrowableInfo::ERROR_NO_PREVIOUS_EXCEPTION
    */
    public function getPrevious() : ConvertHelper_ThrowableInfo
    {
        if(isset($this->previous)) {
            return $this->previous;
        }
        
        throw new ConvertHelper_Exception(
            'Cannot get previous exception info: none available.',
            'Always use hasPrevious() before using getPrevious() to avoid this error.',
            self::ERROR_NO_PREVIOUS_EXCEPTION
        );
    }
    
    public function hasCode() : bool
    {
        return !empty($this->code);
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
        
        $string .= ': '.$this->getMessage().PHP_EOL;
        
        foreach($calls as $call) 
        {
            $string .= $call->toString().PHP_EOL;
        }
        
        if($this->hasPrevious())
        {
            $string .= PHP_EOL.PHP_EOL.
            'Previous error:'.PHP_EOL.PHP_EOL.
            $this->previous->toString();
        }
        
        return $string;
    }
    
   /**
    * Retrieves the URL of the page in which the exception
    * was thrown, if applicable: in CLI context, this will
    * return an empty string.
    * 
    * @return string
    */
    public function getReferer() : string
    {
        return $this->referer;
    }
    
   /**
    * Whether the exception occurred in a command line context.
    * @return bool
    */
    public function isCommandLine() : bool
    {
        return $this->getContext() === self::CONTEXT_COMMAND_LINE;
    }
    
   /**
    * Whether the exception occurred during an http request.
    * @return bool
    */
    public function isWebRequest() : bool
    {
        return $this->getContext() === self::CONTEXT_WEB;
    }
    
   /**
    * Retrieves the context identifier, i.e. if the exception
    * occurred in a command line context or regular web request.
    * 
    * @return string
    * 
    * @see ConvertHelper_ThrowableInfo::isCommandLine()
    * @see ConvertHelper_ThrowableInfo::isWebRequest()
    * @see ConvertHelper_ThrowableInfo::CONTEXT_COMMAND_LINE
    * @see ConvertHelper_ThrowableInfo::CONTEXT_WEB
    */
    public function getContext() : string
    {
        return $this->context;
    }
    
   /**
    * Retrieves the date of the exception, and approximate time:
    * since exceptions do not store time, this is captured the 
    * moment the ThrowableInfo is created.
    * 
    * @return \DateTime
    */
    public function getDate() : \DateTime
    {
        return $this->date;
    }
    
   /**
    * Serializes all information on the exception to an
    * associative array. This can be saved (file, database, 
    * session...), and later be restored into a throwable
    * info instance using the fromSerialized() method.
    * 
    * @return array
    * @see ConvertHelper_ThrowableInfo::fromSerialized()
    */
    public function serialize() : array
    {
        $result = array(
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'date' => $this->date->format('Y-m-d H:i:s'),
            'referer' => $this->getReferer(),
            'context' => $this->getContext(),
            'amountCalls' => $this->callsCount,
            'options' => $this->getOptions(),
            'calls' => array(),
            'previous' => null,
        );
        
        if($this->hasPrevious()) {
            $result['previous'] =  $this->previous->serialize();
        }
        
        foreach($this->calls as $call)
        {
            $result['calls'][] = $call->serialize(); 
        }
        
        return $result;
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
    
   /**
    * Retrieves the current folder depth option value.
    * 
    * @return int
    * @see ConvertHelper_ThrowableInfo::setFolderDepth()
    */
    public function getFolderDepth() : int
    {
        $depth = $this->getOption('folder-depth');
        if(!empty($depth)) {
            return $depth;
        }
        
        return 2;
    }
    
   /**
    * Retrieves all function calls that led to the error.
    * @return ConvertHelper_ThrowableInfo_Call[]
    */
    public function getCalls()
    {
        return $this->calls;
    }
    
   /**
    * Returns the amount of function and method calls in the stack trace.
    * @return int
    */
    public function countCalls() : int
    {
        return $this->callsCount;
    }
    
    protected function parseSerialized(array $serialized) : void
    {
        $this->date = new \DateTime($serialized['date']);
        $this->code = $serialized['code'];
        $this->message = $serialized['message'];
        $this->referer = $serialized['referer'];
        $this->context = $serialized['context'];
        $this->callsCount = $serialized['amountCalls'];
        
        $this->setOptions($serialized['options']);
        
        if(!empty($serialized['previous']))
        {
            $this->previous = ConvertHelper_ThrowableInfo::fromSerialized($serialized['previous']);
        }
        
        foreach($serialized['calls'] as $def)
        {
            $this->calls[] = ConvertHelper_ThrowableInfo_Call::fromSerialized($this, $def);
        }
    }
    
    protected function parseException(\Throwable $e) : void
    {
        $this->date = new \DateTime();
        $this->message = $e->getMessage();
        $this->code = intval($e->getCode());
        
        if(!isset($_REQUEST['REQUEST_URI'])) {
            $this->context = self::CONTEXT_COMMAND_LINE;
        }
        
        $previous = $e->getPrevious();
        if(!empty($previous)) {
            $this->previous = ConvertHelper::throwable2info($previous);
        }
        
        if(isset($_SERVER['REQUEST_URI'])) {
            $this->referer = $_SERVER['REQUEST_URI'];
        }
        
        $trace = $e->getTrace();
        
        // add the origin file as entry
        array_unshift($trace, array(
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ));
        
        $idx = 1;
        
        foreach($trace as $entry)
        {
            $this->calls[] = ConvertHelper_ThrowableInfo_Call::fromTrace($this, $idx, $entry);
            
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
