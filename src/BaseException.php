<?php

namespace AppUtils;

class BaseException extends \Exception
{
    protected $details;
    
    public function __construct($message, $details=null, $code=null, $previous=null)
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
    * Displays pertinent information on the exception in
    * the browser, and exits the script.
    */
    public function display()
    {
        if(!headers_sent()) {
            header('Content-type:text/plain; charset=utf-8');
        }
        
        echo $this->getInfo();
        exit;
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
}
