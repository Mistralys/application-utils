<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use DirectoryIterator;

abstract class AbstractPathInfo implements PathInfoInterface
{
    /**
     * @var string
     */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = FileHelper::normalizePath($path);
    }

    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Gets the file name without path, e.g. "filename.txt",
     * or the folder name if it's a folder.
     *
     * @return string
     */
    public function getName() : string
    {
        return basename($this->path);
    }

    public function isFolder() : bool
    {
        if($this->exists())
        {
            return is_dir($this->path);
        }

        return true;
    }

    public function isFile() : bool
    {
        if($this->exists())
        {
            return is_file($this->getPath());
        }

        return pathinfo($this->getPath(), PATHINFO_EXTENSION) !== '';
    }

    public function exists() : bool
    {
        return file_exists($this->path);
    }

    public function isWritable() : bool
    {
        return is_writable($this->path);
    }

    public function isReadable() : bool
    {
        return is_readable($this->path);
    }

    /**
     * @return string
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_REAL_PATH_NOT_FOUND
     */
    public function getRealPath() : string
    {
        $path = realpath($this->path);

        if($path !== false)
        {
            return FileHelper::normalizePath($path);
        }

        throw new FileHelper_Exception(
            sprintf('Real path for [%s] not found.', $this->getName()),
            sprintf('Tried accessing real path for [%s].', $this->getPath()),
            FileHelper::ERROR_REAL_PATH_NOT_FOUND
        );
    }

    private function requireTrue(bool $condition, string $conditionLabel, ?int $errorCode=null) : PathInfoInterface
    {
        if($condition === true)
        {
            return $this;
        }

        if($errorCode === null)
        {
            $errorCode = FileHelper::ERROR_FILE_DOES_NOT_EXIST;
        }

        throw new FileHelper_Exception(
            sprintf('Path [%s] %s.', $this->getName(), $conditionLabel),
            sprintf('Tried accessing the path [%s].', $this->getPath()),
            $errorCode
        );
    }

    /**
     * @param int|null $errorCode
     * @return $this
     * @throws FileHelper_Exception
     */
    public function requireExists(?int $errorCode=null) : PathInfoInterface
    {
        return $this->requireTrue(
            !empty($this->path) && realpath($this->path) !== false,
            'does not exist',
            FileHelper::ERROR_FILE_DOES_NOT_EXIST
        );
    }

    /**
     * @param int|NULL $errorCode
     * @return $this
     * @throws FileHelper_Exception
     */
    public function requireReadable(?int $errorCode=null) : PathInfoInterface
    {
        $this->requireExists($errorCode);

        return $this->requireTrue(
            $this->isReadable(),
            'is not readable',
            FileHelper::ERROR_FILE_NOT_READABLE
        );
    }

    public function requireWritable(?int $errorCode=null) : PathInfoInterface
    {
        return $this->requireTrue(
            $this->isWritable(),
            'is not writable',
            FileHelper::ERROR_PATH_NOT_WRITABLE
        );
    }

    public function requireIsFile() : PathInfoInterface
    {
        return $this->requireTrue(
            $this->isFile(),
            'is not a file',
            FileHelper::ERROR_PATH_IS_NOT_A_FILE
        );
    }

    public function requireIsFolder() : PathInfoInterface
    {
        return $this->requireTrue(
            $this->isFile(),
            'is not a file',
            FileHelper::ERROR_PATH_IS_NOT_A_FILE
        );
    }

    /**
     * @param string|DirectoryIterator $path
     * @return PathInfoInterface
     */
    public static function resolveType($path) : PathInfoInterface
    {
        if($path instanceof DirectoryIterator)
        {
            $path = $path->getPathname();
        }

        if(FolderInfo::is_dir($path))
        {
            return FolderInfo::factory($path);
        }

        return FileInfo::factory($path);
    }
}
