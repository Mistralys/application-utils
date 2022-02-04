<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * @method FolderInfo requireReadable(?int $errorCode = null)
 * @method FolderInfo requireExists(?int $errorCode = null)
 * @method FolderInfo requireWritable(?int $errorCode = null)
 */
class FolderInfo extends AbstractPathInfo
{
    /**
     * @var array<string,FolderInfo>
     */
    private static $infoCache = array();

    /**
     * @param string$path
     * @return FolderInfo
     */
    public static function factory(string $path) : FolderInfo
    {
        if(!isset(self::$infoCache[$path]))
        {
            self::$infoCache[$path] = new FolderInfo($path);
        }

        return self::$infoCache[$path];
    }

    /**
     * Clears the folder cache that keeps track of any folders
     * created via {@see FolderInfo::factory()} for performance
     * reasons.
     *
     * @return void
     */
    public static function clearCache() : void
    {
        self::$infoCache = array();
    }

    /**
     * @param string $path
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_PATH_IS_NOT_A_FOLDER
     */
    public function __construct(string $path)
    {
        parent::__construct($path);

        if(!self::is_dir($this->path))
        {
            throw new FileHelper_Exception(
                'Not a folder',
                sprintf('The path is not a folder: [%s].', $this->path),
                FileHelper::ERROR_PATH_IS_NOT_A_FOLDER
            );
        }
    }

    /**
     * Detects if the target path is a folder. If the folder
     * does not exist, returns true if the path does not
     * contain a file extension.
     *
     * @param string $path
     * @return bool
     */
    public static function is_dir(string $path) : bool
    {
        $path = trim($path);

        if($path === '' || $path === '.' || $path === '..')
        {
            return false;
        }

        if(is_dir($path))
        {
            return true;
        }

        $path = FileHelper::normalizePath($path);

        return pathinfo($path, PATHINFO_EXTENSION) === '';
    }

    /**
     * @return $this
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_DELETE_FOLDER
     */
    public function delete() : FolderInfo
    {
        if(!$this->exists())
        {
            return $this;
        }

        if(rmdir($this->path))
        {
            return $this;
        }

        throw new FileHelper_Exception(
            sprintf(
                'Cannot delete folder [%s].',
                $this->getName()
            ),
            sprintf(
                'The folder could not be deleted at path: [%s]',
                $this->getPath()
            ),
            FileHelper::ERROR_CANNOT_DELETE_FOLDER
        );
    }

    /**
     * @return $this
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_CREATE_FOLDER
     */
    public function create() : FolderInfo
    {
        if(is_dir($this->path) || mkdir($this->path, 0777, true) || is_dir($this->path))
        {
            return $this;
        }

        throw new FileHelper_Exception(
            sprintf(
                'Could not create target folder [%s].',
                $this->getName()
            ),
            sprintf(
                'Tried to create the folder in path [%s].',
                $this->getPath()
            ),
            FileHelper::ERROR_CANNOT_CREATE_FOLDER
        );
    }
}