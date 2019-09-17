<?php

namespace AppUtils;

class SVNHelper_Command_Commit extends SVNHelper_Command
{
    protected $message = '';
    
    protected function _execute()
    {
        $result = $this->execCommand(
            'commit', 
            $this->target->getPath(), 
            array(
                'message "'.$this->message.'"'
            )
        );
        
        if($result->isError()) {
            $this->throwExceptionUnexpected($result);
        }
        
        return $result;
    }
    
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
}