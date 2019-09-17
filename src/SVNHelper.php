<?php
/**
 * File containing the {@link SVNHelper} class.
 * 
 * @package Application Utils
 * @subpackage SVNHelper
 * @see SVNHelper
 */

namespace AppUtils;

/**
 * Simple helper class to work with SVN repositories.
 * Implements only basic SVN commands, like update and
 * commit.
 *
 * @package Application Utils
 * @subpackage SVNHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see SVNHelper_Exception
 * @see SVNHelper_CommandException
 */
class SVNHelper
{
    const ERROR_LOCAL_PATH_DOES_NOT_EXIST = 22401;
    
    const ERROR_INVALID_REP_URL = 22402;
    
    const ERROR_PATH_IS_OUTSIDE_REPOSITORY = 22403;
    
    const ERROR_TARGET_FOLDER_IS_A_FILE = 22404;
    
    const ERROR_CANNOT_ADD_INEXISTENT_FILE = 22405;
    
    const TARGET_PATH_NOT_FOUND = 22406;
    
   /**
    * @var SVNHelper_Target_Folder
    */
    protected $target;
    
    protected $path;
    
    protected $url;
    
    protected $user;
    
    protected $pass;
    
    protected $options = array(
        'binaries-path' => ''
    );
    
    protected $isWindows = false;
    
    protected $normalize = array(
        'from' => '\\',
        'to' => '/'
    );
    
    public function __construct($repPath, $repURL)
    {
        $this->isWindows = substr(PHP_OS, 0, 3) == 'WIN';
        
        if($this->isWindows) {
            $this->normalize['from'] = '/';
            $this->normalize['to'] = '\\';
        }

        // in case of symlinks, we need to store the original
        // path so we can correctly adjust paths later on.
        $this->sourcePath = $this->normalizePath($repPath);
        
        // ensure that the path exists in the filesystem, thanks to
        // realpath with the actual filesystem case even if the source
        // path case does not entirely match. 
        //
        // NOTE: In case of symlinks, this resolves the symlink to its source (WIN/NIX)
        $realPath = realpath($this->sourcePath);
        if(!is_dir($realPath)) {
            throw new SVNHelper_Exception(
                'Local repository path does not exist',
                sprintf(
                    'Could not find the path [%s] on disk.',
                    $repPath
                ),
                self::ERROR_LOCAL_PATH_DOES_NOT_EXIST
            );
        }
        
        $this->path = $this->normalizePath($realPath);
        $this->target = $this->getFolder('');
        $this->url = $repURL;
        
        $result = array();
        preg_match_all('%([^:]+):(.+)@(https|http|svn)://(.+)%sm', $repURL, $result, PREG_PATTERN_ORDER);
        
        if(!isset($result[1]) || !isset($result[1][0])) {
            throw new SVNHelper_Exception(
                'Invalid SVN repository URL',
                'The SVN URL must have the following format: [username:password@http://domain.com/path/to/rep].',
                self::ERROR_INVALID_REP_URL
            );
        }
        
        $this->pass = $result[2][0];
        $this->user = $result[1][0];
        $this->url = $result[3][0].'://'.$result[4][0];
    }
    
    public function getAuthUser()
    {
        return $this->user;
    }
    
    public function getAuthPassword()
    {
        return $this->pass;
    }
    
   /**
    * Normalizes slashes in the path according to the
    * operating system, i.e. forward slashes for NIX-systems
    * and backward slashes for Windows.
    *
    * @param string $path An absolute path to normalize
    * @param bool $relativize Whether to return a path relative to the repository
    * @throws SVNHelper_Exception
    * @return string
    */
    public function normalizePath($path, $relativize=false)
    {
        if(empty($path)) {
            return '';
        }
        
        if($relativize) 
        {
            $path = $this->normalizePath($path);

            // path is absolute, and does not match the realpath or the source path?
            if(strstr($path, ':'.$this->getSlash()) && (!stristr($path, $this->path) && !stristr($path, $this->sourcePath))) {
                throw new SVNHelper_Exception(
                    'Cannot relativize path outside of repository',
                    sprintf(
                        'The path [%s] is outside of the repository [%s].',
                        $path, 
                        $this->path
                    ),
                    self::ERROR_PATH_IS_OUTSIDE_REPOSITORY
                );
            }
            
            $path = str_replace(array($this->path, $this->sourcePath), '', $path);
            return ltrim($path, $this->normalize['to']);
        }
        
        return str_replace(
            $this->normalize['from'], 
            $this->normalize['to'], 
            $path
        );
    }
    
   /**
    * Retrieves the path slash style according to the
    * current operating system.
    * 
    * @return string
    */
    public function getSlash()
    {
        return $this->normalize['to'];
    }
    
   /**
    * Keeps instances of files.
    * @var SVNHelper_Target[]
    */
    protected $targets = array();
    
   /**
    * Retrieves a file instance from the SVN repository:
    * this allows all possible operations on the file as
    * well as accessing more information on it.
    * 
    * @param string $path A path to the file, relative to the repository path or absolute.
    * @return SVNHelper_Target_File
    * @throws SVNHelper_Exception
    */
    public function getFile($path)
    {
        $path = $this->filterPath($path);
        
        return $this->getTarget('File', $this->relativizePath($path));
    }

   /**
    * Retrieves a folder instance from the SVN repository:
    * This allows all possible operations on the folder as
    * well as accessing more information on it.
    * 
    * @param string $path
    * @return SVNHelper_Target_Folder
    * @throws SVNHelper_Exception
    */
    public function getFolder($path)
    {
        $path = $this->filterPath($path);
        
        return $this->getTarget('Folder', $this->relativizePath($path));
    }
    
   /**
    * Passes the path through realpath and ensures it exists.
    *
    * @param string $path
    * @throws SVNHelper_Exception
    * @return string
    */
    protected function filterPath($path)
    {
        if(empty($path)) {
            return '';
        }
        
        $path = $this->getPath().'/'.$this->relativizePath($path);
        
        $real = realpath($path);
        if($real !== false) {
            return $real;
        }
        
        throw new SVNHelper_Exception(
            'Target file does not exist',
            sprintf(
                'Could not find file [%s] on disk in SVN repository [%s].',
                $path,
                $this->getPath()
            ),
            self::TARGET_PATH_NOT_FOUND
        );
    }
    
   /**
    * Retrieves a target file or folder within the repository.
    *
    * @param string $type The target type, "File" or "Folder".
    * @param string $relativePath A path relative to the root folder.
    * @return SVNHelper_Target
    */
    protected function getTarget($type, $relativePath)
    {
        $key = $type.':'.$relativePath;
        
        $relativePath = $this->normalizePath($relativePath, true);
        if(isset($this->targets[$key])) {
            return $this->targets[$key];
        }
        
        $typeClass = 'SVNHelper_Target_'.$type;
        
        $target = new $typeClass($this, $relativePath);
        $this->targets[$key] = $target;
        
        return $target;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getURL()
    {
        return $this->url;
    }
    
   /**
    * Updates the whole SVN repository from the root folder.
    * @return SVNHelper_CommandResult
    */
    public function runUpdate()
    {
        return $this->createUpdate($this->target)->execute();
    }
    
   /**
    * Creates an update command for the target file or folder.
    * This can be configured further before it is executed.
    * 
    * @param SVNHelper_Target $target
    * @return SVNHelper_Command_Update
    */
    public function createUpdate(SVNHelper_Target $target)
    {
        return $this->createCommand('Update', $target);
    }
    
   /**
    * Creates an add command for the targt file or folder.
    * 
    * @param SVNHelper_Target $target
    * @return SVNHelper_Command_Add
    */
    public function createAdd(SVNHelper_Target $target)
    {
        return $this->createCommand('Add', $target);
    }

    /**
     * Creates an info command for the target file or folder.
     *
     * @param SVNHelper_Target $target
     * @return SVNHelper_Command_Info
     */
    public function createInfo(SVNHelper_Target $target)
    {
        return $this->createCommand('Info', $target);
    }
    
   /**
    * Creates a status command for the target file or folder.
    * 
    * @param SVNHelper_Target $target
    * @return SVNHelper_Command_Status
    */
    public function createStatus(SVNHelper_Target $target)
    {
        return $this->createCommand('Status', $target);
    }

    /**
     * Creates a commit command for the target file or folder.
     *
     * @param SVNHelper_Target $target
     * @return SVNHelper_Command_Commit
     */
    public function createCommit(SVNHelper_Target $target, $message)
    {
        return $this->createCommand('Commit', $target)->setMessage($message);
    }
    
    protected function createCommand($type, SVNHelper_Target $target)
    {
        $class = 'SVNHelper_Command_'.$type;

        $this->requireClass($class);
        
        $cmd = new $class($this, $target);
        return $cmd;
    }
    
   /**
    * Creates a path relative to the repository for the target
    * file or folder, from an absolute path.
    *
    * @param string $path An absolute path.
    * @return string
    */
    public function relativizePath($path)
    {
        return $this->normalizePath($path, true);
    }
    
   /**
    * Adds a folder: creates it as necessary (recursive),
    * and adds it to be committed if it is not versioned yet.
    * Use this instead of {@link getFolder()} when you are
    * not sure that it exists yet, and will need it.
    * 
    * @param string $path Absolute or relative path to the folder
    * @throws SVNHelper_Exception
    * @return SVNHelper_Target_Folder
    */
    public function addFolder($path)
    {
        if(is_dir($path)) {
            return $this->getFolder($path);
        }
        
        $path = $this->relativizePath($path);
        $tokens = explode($this->getSlash(), $path);
        
        $target = $this->path;
        foreach($tokens as $folder) 
        {
            $target .= $this->getSlash().$folder;
            if(file_exists($target)) 
            {
                if(!is_dir($target)) {
                    throw new SVNHelper_Exception(
                        'Target folder is a file',
                        sprintf(
                            'The folder [%s] is actually a file.',
                            $folder
                        ),
                        self::ERROR_TARGET_FOLDER_IS_A_FILE
                    );
                }
                
                continue;
            }
            
            if(!mkdir($target, 0777)) {
                throw new SVNHelper_Exception(
                    'Cannot create folder',
                    sprintf(
                        'Could not create the folder [%s] on disk.',
                        $target
                    )
                );
            }
            
            // we commit the new folder directly, since we
            // will need it later.
            $this->addFolder($target)->runCommit('Added for nested files.');
        }
        
        return $this->getFolder($path)->runAdd();
    }
    
    protected static $logCallback;

   /**
    * Sets the callback function/method to use for
    * SVH helper log messages. This gets the message
    * and the SVNHelper instance as parameters.
    * 
    * @param callable $callback
    * @throws SVNHelper_Exception
    */
    public static function setLogCallback($callback)
    {
        if(!is_callable($callback)) {
            throw new SVNHelper_Exception(
                'Not a valid logging callback',
                'The specified argument is not callable.',
                self::ERROR_INVALID_LOG_CALLBACK
            );
        }
        
        self::$logCallback = $callback;
    }
    
    public static function log($message)
    {
        if(isset(self::$logCallback)) {
            call_user_func(self::$logCallback, 'SVNHelper | '.$message, $this);
        }
    }

   /**
    * Retrieves information about the file, and adds it
    * to be committed later if it not versioned yet. 
    * 
    * @param string $path
    * @return SVNHelper_Target_File
    */
    public function addFile($path)
    {
        return $this->getFile($path)->runAdd();        
    }
    
   /**
    * Commits all changes in the repository.
    * @param string $message The commit message to log.
    */
    public function runCommit($message)
    {
        $this->createCommit($this->getFolder($this->path), $message)->execute();
    }

    protected static $loggers = array();
    
    public static function registerExceptionLogger($callback)
    {
        self::$loggers[] = $callback;
    }

    public static function getExceptionLoggers()
    {
        return self::$loggers;
    }
}
