<?php

declare(strict_types=1);

namespace AppUtils;

use Exception;
use Throwable;

class ConvertHelper_ThrowableInfo implements Interface_Optionable
{
    use Traits_Optionable;
    
    const ERROR_NO_PREVIOUS_EXCEPTION = 43301;
    const ERROR_INVALID_SERIALIZED_DATA_TYPE = 43302;
    
    const FORMAT_HTML = 'html';

    const CONTEXT_COMMAND_LINE = 'cli';
    const CONTEXT_WEB = 'web';

    const SERIALIZED_CODE = 'code';
    const SERIALIZED_MESSAGE = 'message';
    const SERIALIZED_REFERER = 'referer';
    const SERIALIZED_CONTEXT = 'context';
    const SERIALIZED_AMOUNT_CALLS = 'amountCalls';
    const SERIALIZED_DATE = 'date';
    const SERIALIZED_PREVIOUS = 'previous';
    const SERIALIZED_CALLS = 'calls';
    const SERIALIZED_OPTIONS = 'options';

    /**
    * @var Throwable
    */
    protected $exception;
    
   /**
    * @var ConvertHelper_ThrowableInfo_Call[]
    */
    protected $calls = array();
    
   /**
    * @var integer
    */
    protected $code = 0;
    
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
    * @var Microtime
    */
    protected $date;
    
   /**
    * @var string
    */
    protected $context = self::CONTEXT_WEB;

    /**
     * @param array<string,mixed>|Throwable $subject
     */
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
    
    public static function fromThrowable(Throwable $e) : ConvertHelper_ThrowableInfo
    {
        return new ConvertHelper_ThrowableInfo($e);
    }

    /**
     * @param array<string,mixed> $serialized
     * @return ConvertHelper_ThrowableInfo
     */
    public static function fromSerialized(array $serialized) : ConvertHelper_ThrowableInfo
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
    * Improved text-only exception trace.
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
    * @return Microtime
    */
    public function getDate() : Microtime
    {
        return $this->date;
    }
    
   /**
    * Serializes all information on the exception to an
    * associative array. This can be saved (file, database, 
    * session...), and later be restored into a throwable
    * info instance using the fromSerialized() method.
    * 
    * @return array<string,mixed>
    * @see ConvertHelper_ThrowableInfo::fromSerialized()
    */
    public function serialize() : array
    {
        $result = array(
            self::SERIALIZED_MESSAGE => $this->getMessage(),
            self::SERIALIZED_CODE => $this->getCode(),
            self::SERIALIZED_DATE => $this->date->getISODate(),
            self::SERIALIZED_REFERER => $this->getReferer(),
            self::SERIALIZED_CONTEXT => $this->getContext(),
            self::SERIALIZED_AMOUNT_CALLS => $this->callsCount,
            self::SERIALIZED_OPTIONS => $this->getOptions(),
            self::SERIALIZED_CALLS => array(),
            self::SERIALIZED_PREVIOUS => null,
        );
        
        if($this->hasPrevious()) {
            $result[self::SERIALIZED_PREVIOUS] =  $this->previous->serialize();
        }
        
        foreach($this->calls as $call)
        {
            $result[self::SERIALIZED_CALLS][] = $call->serialize();
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
    * Retrieves all function calls that led to the error,
    * ordered from latest to earliest (the first one in
    * the stack is actually the last call).
    *
    * @return ConvertHelper_ThrowableInfo_Call[]
    */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * Retrieves the last call that led to the error.
     *
     * @return ConvertHelper_ThrowableInfo_Call
     */
    public function getFinalCall() : ConvertHelper_ThrowableInfo_Call
    {
        return $this->calls[0];
    }
    
   /**
    * Returns the amount of function and method calls in the stack trace.
    * @return int
    */
    public function countCalls() : int
    {
        return $this->callsCount;
    }


    /**
     * @param array<string,mixed> $serialized
     * @throws Exception
     */
    protected function parseSerialized(array $serialized) : void
    {
        $this->validateSerializedData($serialized);

        $this->date = new Microtime($serialized[self::SERIALIZED_DATE]);
        $this->code = $serialized[self::SERIALIZED_CODE];
        $this->message = $serialized[self::SERIALIZED_MESSAGE];
        $this->referer = $serialized[self::SERIALIZED_REFERER];
        $this->context = $serialized[self::SERIALIZED_CONTEXT];
        $this->callsCount = $serialized[self::SERIALIZED_AMOUNT_CALLS];
        
        $this->setOptions($serialized[self::SERIALIZED_OPTIONS]);
        
        if(!empty($serialized[self::SERIALIZED_PREVIOUS]))
        {
            $this->previous = ConvertHelper_ThrowableInfo::fromSerialized($serialized[self::SERIALIZED_PREVIOUS]);
        }
        
        foreach($serialized[self::SERIALIZED_CALLS] as $def)
        {
            $this->calls[] = ConvertHelper_ThrowableInfo_Call::fromSerialized($this, $def);
        }
    }
    
    protected function parseException(Throwable $e) : void
    {
        $this->date = new Microtime();
        $this->message = $e->getMessage();

        $code = $e->getCode();

        if(is_integer($code))
        {
            $this->code = $code;
        }
        else
        {
            $this->message = 'Original error code: ['.$code.']. ';
        }

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

    /**
     * Retrieves the class name of the exception.
     *
     * @return string
     */
    public function getClass() : string
    {
        return get_class($this->exception);
    }

    /**
     * Converts the exception's information into a human-
     * readable string containing the exception's essentials.
     *
     * It includes any previous exceptions as well, recursively.
     *
     * @param bool $withDeveloperInfo Whether to include developer-specific info
     *                                when available (which may include sensitive
     *                                information).
     * @return string
     * @throws ConvertHelper_Exception
     */
    public function renderErrorMessage(bool $withDeveloperInfo=false) : string
    {
        $finalCall = $this->getFinalCall();

        $message = sb()
            ->t('A %1$s exception occurred.', $this->getClass())
            ->eol()
            ->t('Code:')
            ->add($this->getCode())
            ->t('Message:')
            ->add($this->getMessage());

        if($withDeveloperInfo)
        {
            $message
            ->eol()
            ->t('Final call:')
            ->add($finalCall->toString());
        }

        if($withDeveloperInfo && $this->hasDetails())
        {
            $message
                ->t('Developer details:')
                ->eol()
                ->add($this->getDetails());
        }

        $previous = $this->getPrevious();

        if($previous !== null)
        {
            $message
                ->eol()
                ->eol()
                ->t('Previous exception:')
                ->eol()
                ->add($previous->renderErrorMessage($withDeveloperInfo));
        }

        return (string)$message;
    }

    public function getDetails() : string
    {
        if($this->exception instanceof BaseException)
        {
            return $this->exception->getDetails();
        }

        return '';
    }

    public function hasDetails() : bool
    {
        return $this->exception instanceof BaseException;
    }
    
    public function __toString()
    {
        return $this->toString();
    }

    private function validateSerializedData(array $serialized)
    {
        $keys = array(
            self::SERIALIZED_CODE => 'integer',
            self::SERIALIZED_MESSAGE => 'string',
            self::SERIALIZED_DATE => 'string',
            self::SERIALIZED_REFERER => 'string',
            self::SERIALIZED_CONTEXT => 'string',
            self::SERIALIZED_AMOUNT_CALLS => 'integer',
            self::SERIALIZED_OPTIONS => 'array',
            self::SERIALIZED_CALLS => 'array'
        );

        foreach($keys as $key => $type)
        {
            if(!isset($serialized[$key]) || gettype($serialized[$key]) !== $type)
            {
                throw $this->createTypeException($key, $type);
            }
        }
    }

    private function createTypeException(string $keyName, string  $expectedType) : ConvertHelper_Exception
    {
        return new ConvertHelper_Exception(
            'Invalid serialized throwable key',
            sprintf(
                'The key [%s] does not have the expected data type [%s].',
                $keyName,
                $expectedType
            ),
            self::ERROR_INVALID_SERIALIZED_DATA_TYPE
        );
    }
}
