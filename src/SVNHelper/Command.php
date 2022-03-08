<?php

namespace AppUtils;

abstract class SVNHelper_Command
{
    public const ERROR_INVALID_COMMAND_RESULT = 22601;
    
    public const ERROR_UNEXPECTED_OUTPUT = 22602;
    
    public const ERROR_CONFLICTS_REPORTED = 22603;
    
    public const ERROR_REPOSITORY_LOCKED = 22604;
    
    public const ERROR_CONNECTION_FAILED = 22605;
    
    public const SVN_ERROR_IGNORED = 8000001;
    
    public const SVN_ERROR_TYPE_ERROR = 'error';
    
    public const SVN_ERROR_TYPE_WARNING = 'warning';
    
    /**
    * @var SVNHelper
    */
    protected $helper;
    
   /**
    * @var SVNHelper_Target
    */
    protected $target;
    
   /**
    * @var SVNHelper_CommandResult|NULL
    */
    protected ?SVNHelper_CommandResult $result;
    
    public function __construct(SVNHelper $helper, SVNHelper_Target $target)
    {
        $this->helper = $helper;
        $this->target = $target;
    }
    
   /**
    * @return SVNHelper
    */
    public function getSVN()
    {
        return $this->helper;
    }
    
    public function getTarget()
    {
        return $this->target;
    }
    
    public function execute()
    {
        if(isset($this->result))
        {
            return $this->result;
        }
        
        // adjust environment locale for the SVN unicode features to work properly.
        $locale = 'en_US.UTF-8';
        setlocale(LC_ALL, $locale);
        putenv('LC_ALL='.$locale);
        
        $this->result = $this->_execute();
        if(!$this->result instanceof SVNHelper_CommandResult) {
            throw new SVNHelper_Exception(
                'Not a valid SVN command result',
                sprintf(
                    'The command [%s] did not return an [SVNHelper_CommandResult] instance.',
                    get_class($this)
                ),
                self::ERROR_INVALID_COMMAND_RESULT
            );
        }
        
        return $this->result;
    }
    
    abstract protected function _execute();

    protected function buildCommand($mode, $path=null, $params=array())
    {
        $params[] = 'non-interactive';
        $params[] = 'username '.$this->helper->getAuthUser();
        $params[] = 'password '.$this->helper->getAuthPassword();
        
        $cmd = 'svn '.$mode.' '.$path.' ';
        
        foreach($params as $param) {
            $cmd .= '--'.$param.' ';
        }
        
        $cmd .= '2>&1';
        
        return $cmd;
    }

   /**
    * Executes the specified command, and returns a result
    * instance to read the results.
    * 
    * @param string $mode The command mode, e.g. commit / update...
    * @param string $path The path to apply the command to
    * @param array $params
    * @return SVNHelper_CommandResult
    */
    protected function execCommand($mode, $path=null, $params=array())
    {
        $relative = $this->helper->relativizePath($path);
        
        $this->log(sprintf(
            '[%s] | Executing ['.$mode.'].',
            $relative
        ));
        
        $cmd = $this->buildCommand($mode, $path, $params);
        
        $output = array();
        exec($cmd, $output);
        
        $lines = array();
        foreach($output as $line) {
            $lines[] = mb_strtolower(trim(utf8_encode($line)));
        }
        
        $errorMessages = array();
        
        // command was skipped for some reason. We have to check
        // for it this way, because this error is not shown like
        // other regular errors.
        //
        // Can happen for example when the path is not under version
        // control.
        if(isset($lines[0]) && substr($lines[0], 0, 7) == 'skipped')
        {
            $tokens = explode('--', $lines[0]);
            $message = trim(array_pop($tokens));
            
            $error = new SVNHelper_CommandError(
                $this, 
                self::SVN_ERROR_TYPE_ERROR, 
                $message, 
                self::SVN_ERROR_IGNORED
            );
            
            $errorMessages[] = $error;
        }
        // search for error codes. SVN adds lines in
        // the following format:
        //
        // svn: e123456: error message
        // svn: w123456: warning message
        else
        {
            foreach($lines as $line) 
            {
                if(strstr($line, 'svn:')) 
                {
                    $result = array();
                    preg_match_all('/svn:[ ]*(e|warning:[ ]*w)([0-9]+):(.*)/', $line, $result, PREG_PATTERN_ORDER);
                    
                    if(isset($result[1]) && isset($result[1][0])) 
                    {
                        $message = trim($result[3][0]);
                        $code = trim($result[2][0]);
                        $type = self::SVN_ERROR_TYPE_ERROR;
                        
                        if($result[1][0] != 'e') {
                            $type = self::SVN_ERROR_TYPE_WARNING;
                        }

                        $error = new SVNHelper_CommandError($this, $type, $message, $code);
                        $errorMessages[] = $error;
                    }
                }
            }
        }
        
        $result = new SVNHelper_CommandResult($this, $cmd, $lines, $errorMessages);
        
        if($result->isError()) {
            $this->log(sprintf('[%s] | Command returned errors.', $relative));
        } 
        
        return $result;
    }
    
    public function getResult()
    {
        return $this->result;
    }
    
   /**
    * Retrieves the type of command, e.g. "Commit"
    * @return string
    */
    public function getType()
    {
        return str_replace('SVNHelper_Command_', '', get_class($this));   
    }
    
    protected function throwExceptionUnexpected(SVNHelper_CommandResult $result)
    {
        if($result->isConnectionFailed()) {
            $this->throwException(
                t('Could not connect to the remote SVN repository'), 
                '', 
                self::ERROR_CONNECTION_FAILED, 
                $result
            );
        }
        
        if($result->hasConflicts()) {
            $this->throwException(
                t('SVN command reported conflicts'), 
                '', 
                self::ERROR_CONFLICTS_REPORTED, 
                $result
            );
        }
        
        if($result->hasLocks()) {
            $this->throwException(
                t('The target SVN folder or file is locked.'), 
                '', 
                self::ERROR_REPOSITORY_LOCKED, 
                $result
            );
        }
        
        $this->throwException(
            t('SVN command returned unexpected errors'),
            '',
            self::ERROR_UNEXPECTED_OUTPUT,
            $result
        );
    }
    
    protected function throwException($message, $details, $code, SVNHelper_CommandResult $result, $previous=null)
    {
        $body = 
        '<p>'.
            'Command: '.$this->getType().
        '</p>'.
        '<p>'.
            'Details:'.
        '</p>'.
        '<p>'.$details.'</p>'.
        '<p>'.
            'Result error messages:'.
        '</p>'.
        '<ul>';
            $errors = $result->getErrorMessages();
            foreach($errors as $error) {
                $body .= 
                '<li>'.
                    $error.
                '</li>';
            }
            $body .=
        '</ul>'.
        '<p>'.
            'Raw SVN command line output:'.
        '</p>'.
        '<pre>'.implode(PHP_EOL, $result->getOutput()).'</pre>'.
        '<p>'.
            'Command line:'.
        '</p>'.
        '<code>'.$result->getCommandLine().'</code>';
         
            
        throw new SVNHelper_CommandException($message, $body, $code, $result, $previous);
    }
    
    protected function log($message)
    {
        SVNHelper::log($message);
    }
}