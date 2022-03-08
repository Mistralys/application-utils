<?php

namespace AppUtils;

class SVNHelper_CommandError
{
    /**
     * @var SVNHelper_Command
     */
    protected SVNHelper_Command $command;
    
    /**
     * @var string
     */
    protected string $type;
    
    /**
     * @var string
     */
    protected string $code;
    
    /**
     * @var string
     */
    protected string $message;

    /**
     * @param SVNHelper_Command $command
     * @param string $type
     * @param string $message
     * @param string|int $code
     */
    public function __construct(SVNHelper_Command $command, string $type, string $message, $code)
    {
        $this->command = $command;
        $this->type = $type;
        $this->message = $message;
        $this->code = $this->filterCode($code);
    }
    
    public function getType() : string
    {
        return $this->type;
    }
    
    public function isError() : bool
    {
        return $this->isType(SVNHelper_Command::SVN_ERROR_TYPE_ERROR);
    }
    
    public function isWarning() : bool
    {
        return $this->isType(SVNHelper_Command::SVN_ERROR_TYPE_WARNING);
    }
    
    public function isType(string $type) : bool
    {
        return $this->type === $type;
    }
    
    public function getMessage() : string
    {
        return $this->message;
    }
    
    public function getCode() : string
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
    
    public function isConflict() : bool
    {
        return stristr($this->message, 'conflict');
    }
    
    public function isLock() : bool
    {
        return $this->hasAnyErrorCode(array('155004', '195022'));
    }
    
    public function isConnectionFailed() : bool
    {
        return $this->hasAnyErrorCode(array('170013', '215004'));
    }

    /**
     * @param string|int $code
     * @return string
     */
    private function filterCode($code) : string
    {
        return ltrim((string)$code, 'e');
    }

    /**
     * @param string|int $code
     * @return bool
     */
    public function hasErrorCode($code) : bool
    {
        return $this->code === $this->filterCode($code);
    }
    
    /**
     * Checks whether the result has any of the error codes.
     * @param string[]|int[] $codes SVN style error codes, e.g. "e1234" or just the error number, e.g. "1234"
     * @return boolean
     */
    public function hasAnyErrorCode(array $codes) : bool
    {
        $items = array();
        foreach($codes as $code) {
            $items[] = $this->filterCode($code);
        }
        
        return in_array($this->code, $items, true);
    }
}