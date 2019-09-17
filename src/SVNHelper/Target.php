<?php

require_once 'SVNHelper/Target/File.php';

require_once 'SVNHelper/Target/Folder.php';

abstract class SVNHelper_Target
{
    const ERROR_FILE_NOT_FOUND = 22501;
    
    /**
     * @var SVNHelper
     */
    protected $helper;
    
    /**
     * The relative path to the file.
     * @var string
     */
    protected $relativePath;
    
    protected $path;
    
    protected $url;
    
    public function __construct(SVNHelper $helper, $relativePath)
    {
        $this->helper = $helper;
        $this->relativePath = $relativePath;
        $this->url = $this->helper->getURL().'/'.$this->relativePath;
        
        $path = $this->helper->getPath();
        if(!empty($relativePath)) {
            $path .= $this->helper->getSlash().$this->relativePath;
        }
        
        // ensure that the path is correct, even if the case is not quite
        // correct: realpath checks the path in a case insensitive way and
        // returns it in the actual case (at least on NIX-systems, where
        // this is relevant).
        $this->path = realpath($path);
        
        if(!$this->path || !file_exists($this->path)) {
            throw new SVNHelper_Exception(
                'File not found',
                sprintf(
                    'Could not find file [%s] on disk in SVN repository [%s].',
                    $path,
                    $this->helper->getPath()
                ),
                self::ERROR_FILE_NOT_FOUND
            );
        }
    }
    
    public function getRelativePath()
    {
        return $this->relativePath;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getURL()
    {
        return $this->url;
    }
    
    public function isFile()
    {
        return $this instanceof SVNHelper_Target_File;
    }
    
    public function isFolder()
    {
        return $this instanceof SVNHelper_Target_Folder;
    }
    
   /**
    * Runs an update of the file or folder.
    * @return SVNHelper_Command_Update
    */
    public function runUpdate()
    {
        $cmd = $this->createUpdate();
        $cmd->execute();
        
        return $cmd;
    }
    
   /**
    * Creates an update command instance for the target file or folder.
    * @return SVNHelper_Command_Update
    */
    public function createUpdate()
    {
        return $this->helper->createUpdate($this);
    }
    
   /**
    * Creates a status command instance for the target file or folder.
    * @return SVNHelper_Command_Status
    */
    public function createStatus()
    {
        return $this->helper->createStatus($this);
    }
    
   /**
    * Creates an info command instance for the target file or folder.
    * @return SVNHelper_Command_Info
    */
    public function createInfo()
    {
        return $this->helper->createInfo($this);
    }
    
   /**
    * Creates a commit command instance for the target file or folder.
    * @param string $message
    * @return SVNHelper_Command_Commit
    */
    public function createCommit($message)
    {
        return $this->helper->createCommit($this, $message);
    }

   /**
    * Creates an add command instance for the target file or folder.
    * @return SVNHelper_Command_Add
    */
    public function createAdd()
    {
        return $this->helper->createAdd($this);
    }
    
   /**
    * @return SVNHelper_Command_Status
    */
    public function getStatus()
    {
        $cmd = $this->createStatus();
        $cmd->execute();
        return $cmd;
    }
    
    public function runAdd()
    {
        if(!$this->isVersioned()) {
            $this->createAdd()->execute();
            $this->clearCache();
        }
        
        return $this;
    }
    
   /**
    * Whether the target is versioned or needs to be added.
    * @return boolean
    */
    public function isVersioned()
    {
        return $this->getInfo()->isVersioned();
    }
    
    protected $cache = array();
    
    protected function clearCache()
    {
        $this->cache = array();
    }
    
   /**
    * Retrieves information on the target.
    * @return SVNHelper_Command_Info
    */
    public function getInfo()
    {
        if(!isset($this->cache['info'])) {
            $this->cache['info'] = $this->helper->createInfo($this);
        }
        
        return $this->cache['info'];
    }
    
   /**
    * Commits the target file or folder. If it has
    * not been added to the repository yet, it is 
    * added automatically beforehand. If it does
    * not need to be committed, no changes are made.
    * 
    * @param string $message
    * @return SVNHelper_Target
    */
    public function runCommit($message)
    {
        if(!$this->isVersioned()) {
            $this->log('Adding the unversioned file.');
            $this->runAdd();
        }
        
        if(!$this->isCommitted()) {
            $this->createCommit($message)->execute();
            $this->clearCache();
        }
        else 
        {
            $this->log('Already committed, nothing to do.');
        }
        
        return $this;
    }
    
    public function isCommitted()
    {
        return $this->getInfo()->isCommitted();
    }
    
    public function getModifiedDate()
    {
        return FileHelper::getModifiedDate($this->getPath());
    }
    
    protected function log($message)
    {
        SVNHelper::log(sprintf(
            '[%s] | %s',
            $this->getRelativePath(),
            $message
        ));
    }
}