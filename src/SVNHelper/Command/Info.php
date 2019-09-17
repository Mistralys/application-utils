<?php

namespace AppUtils;

class SVNHelper_Command_Info extends SVNHelper_Command
{
    protected $params;
    
    protected $status;
    
    protected function _execute()
    {
        $result = $this->execCommand('info', $this->target->getPath());

        $this->params = array();
        
        if($result->isError()) {
            // this error code means the target exists, but is not versioned yet
            if(!$result->hasErrorCode('200009')) {
                $this->throwExceptionUnexpected($result);
            }

            $this->status = 'added'; 
            
            return $result;
        } 
        
        $this->status = 'versioned';
        
        $lines = $result->getOutput();
        
        foreach($lines as $line) 
        {
            if(!strstr($line, ':')) {
                continue;
            }
            
            $pos = strpos($line, ':');
            $name = str_replace(' ', '-', substr($line, 0, $pos));
            $value = trim(substr($line, $pos+1));
            $this->params[$name] = $value;
        }
        
        return $result;
    }
    
   /**
    * Whether the file or folder has already been committed
    * and has no pending changes.
    * 
    * @return boolean
    */
    public function isCommitted()
    {
        $status = $this->target->getStatus();
        return $status->isUnmodified();
    }
    
    public function isVersioned()
    {
        $this->execute();
        
        return $this->status == 'versioned'; 
    }
    
    public function isAdded()
    {
        $this->execute();
        
        return $this->status == 'added';
    }
    
    public function getRevision()
    {
        return $this->getParam('revision');
    }
    
    public function getLocalRevision()
    {
        return $this->getParam('last-changed-rev');
    }
    
    public function getAuthor()
    {
        return $this->getParam('last-changed-author');
    }
    
    protected function getParam($name)
    {
        $this->execute();
        
        if(isset($this->params[$name])) {
            return $this->params[$name];
        }
        
        return null;
    }
}