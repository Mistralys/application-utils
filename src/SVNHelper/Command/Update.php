<?php

require_once 'SVNHelper/Command.php';

class SVNHelper_Command_Update extends SVNHelper_Command
{
    const ERROR_NO_UPDATE_REVISION_SPECIFIED = 23901;

    protected function _execute()
    {
        $result = $this->execCommand('update', $this->target->getPath());
        
        if($result->isError()) {
            $this->throwExceptionUnexpected($result);
        }
        
        $this->parseResult($result);
        
        if(!isset($this->revision)) {
            $this->throwException(
                'No update revision returned',
                'The command did not return the expected last line with "at revision x".',
                self::ERROR_NO_UPDATE_REVISION_SPECIFIED,
                $result
            );
        }
        
        return $result;
    }
    
   /**
    * @var SVNHelper_Command_Update_Status[]
    */
    protected $stati;
    
    protected $revision;
    
   /**
    * Parses the command output to find out which files have been modified, and how.
    * @param SVNHelper_CommandResult $result
    */
    protected function parseResult(SVNHelper_CommandResult $result)
    {
        $this->stati = array();
        
        $lines = $result->getLines();
        
        foreach($lines as $line) 
        {
            $result = array();
            preg_match_all('/\A(a|c|d|u)[ ]+(.+)/si', $line, $result, PREG_PATTERN_ORDER);
            
            // is this a file update status line? It looks like this:
            // a    c:\path\to\file.ext
            // ^ status code
            if(isset($result[0]) && isset($result[1][0]) && !empty($result[1][0])) 
            {
                $status = $result[1][0];
                $path = $result[2][0];
                $obj = new SVNHelper_Command_Update_Status($this, $status, $path);
                
                if(!isset($this->stati[$status])) {
                    $this->stati[$status] = array();
                }
                
                $this->stati[$status][] = $obj;
                
                continue;
            }
            
            // the revision line, "updated to revision X" or "at revision X"
            if(strstr($line, 'revision ')) {
                preg_match('/(at revision|to revision) ([0-9]+)/si', $line, $result);
                if(isset($result[2])) {
                    $this->revision = $result[2];
                }
                continue;
            }
        }
    }
    
    public function hasConflicts()
    {
        return $this->hasStatus('c');
    }
    
    public function hasAdded()
    {
        return $this->hasStatus('a');
    }
    
    public function hasDeleted()
    {
        return $this->hasStatus('d');
    }
    
    public function hasUpdated()
    {
        return $this->hasStatus('u');
    }
    
    public function getConflicted()
    {
        return $this->getByStatus('c');
    }
    
    public function getUpdated()
    {
        return $this->getByStatus('u');
    }
    
    public function getDeleted()
    {
        return $this->getByStatus('d');
    }
    
    public function getAdded()
    {
        return $this->getByStatus('a');
    }
    
   /**
    * Whether there were files with the specified status code.
    * @param string $status
    * @return boolean
    */
    protected function hasStatus($status)
    {
        $this->execute();
        
        return isset($this->stati[$status]);
    }
    
    protected function getByStatus($status)
    {
        $this->execute();
        
        if(isset($this->stati[$status])) {
            return $this->stati[$status];
        }
        
        return array();
    }
    
    public function getRevision()
    {
        $this->execute();
        
        return $this->revision;
    }
}

class SVNHelper_Command_Update_Status
{
    protected $command;
    
    protected $status;
    
    protected $path;
    
    public function __construct(SVNHelper_Command_Update $command, $status, $path)
    {
        $this->command = $command;
        $this->status = $status;
        $this->path = $path;
    }
    
    public function getStatusCode()
    {
        return $this->status;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getFileName()
    {
        return basename($this->path);
    }
    
    public function getRelativePath()
    {
        return $this->getFile()->getRelativePath();
    }
    
    public function getFile()
    {
        return $this->command->getSVN()->getFile($this->path);
    }
}