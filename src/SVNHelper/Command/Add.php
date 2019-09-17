<?php

namespace AppUtils;

class SVNHelper_Command_Add extends SVNHelper_Command
{
    const ERROR_CANNOT_ADD_TARGET_PATH = 23801;
    
    const ERROR_UNEXPECTED_RESULT = 23802;
    
    protected $added = false;
    
    protected function _execute()
    {
        $result = $this->execCommand('add', $this->target->getPath());

        if($result->isError()) {
            throw new SVNHelper_Exception(
                'Could not add target path',
                sprintf(
                    'Adding the target path [%s] returned errors: [%s]',
                    $this->target->getPath(),
                    $result->getErrorMessages(true)
                ),
                self::ERROR_CANNOT_ADD_TARGET_PATH
            );
        }
        
        $line = $result->getFirstLine();
        if(substr($line, 0, 1) != 'a') {
            throw new SVNHelper_Exception(
                'Unexpected result for adding a path',
                sprintf(
                    'Adding the target path [%s] did not yield the expected result. Raw result: %s',
                    $this->target->getPath(),
                    implode(PHP_EOL, $result->getOutput())
                ),
                self::ERROR_UNEXPECTED_RESULT
            );
        }
        
        return $result;
    }
}