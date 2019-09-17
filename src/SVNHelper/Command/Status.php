<?php

namespace AppUtils;

class SVNHelper_Command_Status extends SVNHelper_Command
{
    const STATUS_NOT_MODIFIED = 'not-modified';
    
    const STATUS_UNKNOWN = 'unknown';
    
    const STATUS_MISSING = 'missing';
    
    const STATUS_ADD = 'add';
    
    const STATUS_DELETE = 'delete';
    
    const STATUS_MODIFIED = 'modified';
    
    const STATUS_CONFLICTED = 'conflicted';
    
    const STATUS_UNVERSIONED = 'unversioned';
    
    const STATUS_IGNORED = 'ignored';
    
    const STATUS_FILETYPE_CHANGE = 'filetype-change';
    
   /**
    * @var array
    * @see http://svnbook.red-bean.com/en/1.7/svn.ref.svn.c.status.html
    */
    protected static $knownStati = array(
        'a' => self::STATUS_ADD,
        'd' => self::STATUS_DELETE,
        'm' => self::STATUS_MODIFIED,
        'r' => self::STATUS_MODIFIED,
        'c' => self::STATUS_CONFLICTED,
        'i' => self::STATUS_IGNORED,
        '?' => self::STATUS_UNVERSIONED,
        '!' => self::STATUS_MISSING,
        '~' => self::STATUS_FILETYPE_CHANGE,
    );
    
   /**
    * @var string
    */
    protected $status;
    
    protected function _execute()
    {
        $result = $this->execCommand('status', $this->target->getPath(), array('depth empty'));
        
        if($result->isError()) {
            $this->throwExceptionUnexpected($result);
        }

        $lines = $result->getOutput();
        
        if(empty($lines)) 
        {
            $this->status = self::STATUS_NOT_MODIFIED;
        }
        else 
        {
            $this->status = self::STATUS_UNKNOWN;
            
            $svnStatusCode = strtolower(substr($lines[0], 0, 1));
            if(isset(self::$knownStati[$svnStatusCode])) {
               $this->status = self::$knownStati[$svnStatusCode];
            }
        }
        
        return $result;
    }
    
    public function getStatus()
    {
        $this->execute();
        
        return $this->status;
    }
    
    public function isUnmodified()
    {
        return $this->isStatus(self::STATUS_NOT_MODIFIED);
    }
    
    public function isModified()
    {
        return $this->isStatus(self::STATUS_MODIFIED);
    }
    
    public function isAdded()
    {
        return $this->isStatus(self::STATUS_ADD);
    }
    
    public function isConflicted()
    {
        return $this->isStatus(self::STATUS_CONFLICTED);
    }
    
    protected function isStatus($code)
    {
        $this->execute();
        
        return $this->status == $code;
    }
}