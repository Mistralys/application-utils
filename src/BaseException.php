<?php
/**
 * File containing the {@see AppUtils\BaseException} class.
 *
 * @package Application Utils
 * @subpackage BaseException
 * @see AppUtils\BaseException
 */

namespace AppUtils;

use Throwable;

/**
 * Extended exception class with additional tools. Allows setting
 * developer-only information that does not get shown along with
 * the message, but can easily be retrieved and logged.
 *
 * @package Application Utils
 * @subpackage BaseException
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class BaseException extends \Exception
{
   /**
    * @var string
    */
    protected $details;
    
   /**
    * @param string $message
    * @param string $details
    * @param int $code
    * @param \Exception $previous
    */
    public function __construct(string $message, $details=null, $code=null, $previous=null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->details = $details;
    }
    
   /**
    * Retrieves the detailed error description, if any.
    * @return string
    */
    public function getDetails() : string
    {
        if($this->details !== null) {
            return $this->details;
        }
        
        return '';
    }
    
   /**
    * Displays pertinent information on the exception.
    */
    public function display() : void
    {
        if(!headers_sent()) {
            header('Content-type:text/plain; charset=utf-8');
        }
        
        echo $this->getInfo();
    }
    
   /**
    * Retrieves information on the exception that can be
    * easily accessed.
    * 
    * @return ConvertHelper_ThrowableInfo
    */
    public function getInfo() : ConvertHelper_ThrowableInfo
    {
        return ConvertHelper::throwable2info($this);
    }
    
   /**
    * Dumps a current PHP function trace, as a text only string.
    */
    public static function dumpTraceAsString() : void
    {
        try
        {
            throw new BaseException('');
        }
        catch(BaseException $e) 
        {
            echo self::createInfo($e)->toString();
        }
    }

   /**
    * Dumps a current PHP function trace, with HTML styling.
    */
    public static function dumpTraceAsHTML() : void
    {
        try
        {
            throw new BaseException('');
        }
        catch(BaseException $e)
        {
            echo '<pre style="background:#fff;font-family:monospace;font-size:14px;color:#444;padding:16px;border:solid 1px #999;border-radius:4px;">';
            echo self::createInfo($e)->toString();
            echo '</pre>';
        }
    }
    
   /**
    * Creates an exception info instance from a throwable instance.
    * 
    * @param Throwable $e
    * @return ConvertHelper_ThrowableInfo
    * @see ConvertHelper::throwable2info()
    */
    public static function createInfo(Throwable $e) : ConvertHelper_ThrowableInfo
    {
        return ConvertHelper::throwable2info($e);
    }
}
