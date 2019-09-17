<?php

namespace AppUtils;

class SVNHelper_CommandError
{
    /**
     * @var SVNHelper_Command
     */
    protected $command;
    
    /**
     * @var string
     */
    protected $type;
    
    /**
     * @var integer
     */
    protected $code;
    
    /**
     * @var string
     */
    protected $message;
    
    public function __construct(SVNHelper_Command $command, $type, $message, $code)
    {
        $this->command = $command;
        $this->type = $type;
        $this->message = $message;
        $this->code = $code;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function isError()
    {
        return $this->isType(SVNHelper_Command::SVN_ERROR_TYPE_ERROR);
    }
    
    public function isWarning()
    {
        return $this->isType(SVNHelper_Command::SVN_ERROR_TYPE_WARNING);
    }
    
    public function isType($type)
    {
        return $this->type == $type;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function getCode()
    {
        return $this->code;
    }
    
    public function __toString()
    {
        return sprintf(
            'SVN %s #%s: %s',
            $this->getType(),
            $this->getCode(),
            $this->getMessage()
            );
    }
    
    public function isConflict()
    {
        return stristr($this->message, 'conflict');
    }
    
    public function isLock()
    {
        return $this->hasAnyErrorCode(array('155004', '195022'));
    }
    
    public function isConnectionFailed()
    {
        return $this->hasAnyErrorCode(array('170013', '215004'));
    }
    
    public function hasErrorCode($code)
    {
        $code = ltrim($code, 'e');
        return $this->code === $code;
    }
    
    /**
     * Checks whether the result has any of the error codes.
     * @param string[]|int[] $codes SVN style error codes, e.g. "e1234" or just the error number, e.g. "1234"
     * @return boolean
     */
    public function hasAnyErrorCode($codes)
    {
        $items = array();
        foreach($codes as $code) {
            $items[] = ltrim($code, 'e');
        }
        
        return in_array($this->code, $items);
    }
}