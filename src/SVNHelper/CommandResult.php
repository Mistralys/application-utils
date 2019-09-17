<?php 

namespace AppUtils;

class SVNHelper_CommandResult
{
   /**
    * @var SVNHelper_Command
    */
    protected $command;
    
   /**
    * @var string[]
    */
    protected $output;
    
   /**
    * @var SVNHelper_CommandError[]
    */
    protected $errors = array();
    
   /**
    * @var SVNHelper_CommandError[]
    */
    protected $warnings = array();
    
   /**
    * The actual command that has been executed
    * @var string
    */
    protected $commandLine;
    
   /**
    * @param SVNHelper_Command $command
    * @param string[] $output
    * @param SVNHelper_CommandError[] $errors
    */
    public function __construct(SVNHelper_Command $command, $commandLine, $output, $errors)
    {
        $this->command = $command;
        $this->commandLine = $commandLine;
        $this->output = $output;
        
        foreach($errors as $error) {
            if($error->isError()) {
                $this->errors[] = $error;
            } else {
                $this->warnings[] = $error;
            }
        }
    }
    
    public function isError()
    {
        return !empty($this->errors);
    }
    
    public function isWarning()
    {
        return !empty($this->warnings);
    }
    
    public function hasErrorCode($code)
    {
        foreach($this->errors as $error) {
            if($error->getCode() == $code) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getCommandLine()
    {
        return $this->commandLine;
    }
    
    public function getOutput()
    {
        return $this->output;
    }
    
    public function getCommand()
    {
        return $this->command;
    }
    
   /**
    * Retrieves all error messages.
    * 
    * @param string $asString
    * @return string|string[]
    */
    public function getErrorMessages($asString=false)
    {
        if($asString) {
            $lines = array();
            foreach($this->errors as $error) {
                $lines[] = (string)$error;
            }
            
            return implode(PHP_EOL, $lines);
        }
        
        $messages = array();
        foreach($this->errors as $error) {
            $messages[] = (string)$error;
        }
        
        return $messages;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getWarnings()
    {
        return $this->warnings;
    }
    
    public function getLastLine()
    {
        return $this->output[count($this->output)-1];
    }
    
    public function getFirstLine()
    {
        return $this->output[0];
    }
    
    public function getLines()
    {
        return $this->output;
    }
    
    public function isConnectionFailed()
    {
        foreach($this->errors as $error) {
            if($error->isConnectionFailed()) {
                return true;
            }
        }
        
        return false;
    }
    
    public function hasConflicts()
    {
        foreach($this->errors as $error) {
            if($error->isConflict()) {
                return true;
            }
        }
        
        return false;
    }
    
    public function hasLocks()
    {
        foreach($this->errors as $error) {
            if($error->isLock()) {
                return true;
            }
        }
        
        return false;
    }
}