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
    
    public function getDetails()
    {
        return $this->details;
    }
}