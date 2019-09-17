<?php

/**
 * Exception for SVN commands: only thrown in relation
 * to the current SVN commands being executed.
 *
 * @package Application Utils
 * @subpackage SVNHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see SVNHelper_Exception
 */
class SVNHelper_CommandException extends SVNHelper_Exception
{
    /**
     * @var SVNHelper_CommandResult
     */
    protected $result;
    
    public function __construct($message, $details, $code, SVNHelper_CommandResult $result, $previous=null)
    {
        parent::__construct($message, $details, $code, $previous);
        $this->result = $result;
    }
    
    /**
     * @return SVNHelper_CommandResult
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * @return SVNHelper_Command
     */
    public function getCommand()
    {
        return $this->result->getCommand();
    }
    
    public function getErrorMessages()
    {
        return $this->result->getErrorMessages();
    }
    
    /**
     * Retrieves the type of command, e.g. "Update", "Commit".
     * @return string
     */
    public function getCommandType()
    {
        return $this->getCommand()->getType();
    }
}