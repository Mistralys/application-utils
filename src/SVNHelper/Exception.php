<?php

namespace AppUtils;

/**
 * General purpose SVNHelper exception for any errors
 * regarding the helper operations.
 *
 * @package Application Utils
 * @subpackage SVNHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see SVNHelper_CommandException
 */
class SVNHelper_Exception extends BaseException
{
    protected $id;
    
    protected $logging = true;
    
    public function __construct($message, $details=null, $code=null, $previous=null)
    {
        parent::__construct($message, $details, $code, $previous);
        
        $this->id = md5(microtime(true).'-svnexception-'.$code.'-'.$message);
    }
    
    public function getID()
    {
        return $this->id;
    }
    
    public function __destruct()
    {
        if(!$this->logging) {
            return;
        }
        
        $loggers = SVNHelper::getExceptionLoggers();
        
        if(empty($loggers)) {
            return;
        }
        
        foreach($loggers as $callback) {
            call_user_func($callback, $this);
        }
    }
    
    public function disableLogging()
    {
        $this->logging = false;
    }
}
