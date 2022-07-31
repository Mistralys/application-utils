<?php
/**
 * File containing the class {@see \AppUtils\FileHelper\AbstractPathInfo}.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\AbstractPathInfo
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\Interface_Stringable;
use AppUtils\Interfaces\RenderableInterface;
use DateTime;
use DirectoryIterator;
use SplFileInfo;

/**
 * Abstract implementation of the path info interface.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class AbstractPathInfo implements PathInfoInterface
{
    protected string $path;

    /**
     * @var array<string,mixed>
     */
    private array $runtimeProperties = array();

    public function __construct(string $path)
    {
        $this->path = FileHelper::normalizePath($path);
    }

    /**
     * The full path to the file/folder.
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
     * NOTE: This includes the file extension. To get a
     * file's base name, get a file info instance, and use
     * {@see FileInfo::getBaseName()}.
     *
     * @return string
     */
    public function getName() : string
    {
        return basename($this->path);
    }

    public function isFolder() : bool
    {
        return FolderInfo::is_dir($this->path);
    }

    public function isFile() : bool
    {
        return FileInfo::is_file($this->path);
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
     * The real path without symlinks to the file/folder.
     * @return string
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_REAL_PATH_NOT_FOUND
     */
    public function getRealPath() : string
    {
        $this->requireExists();

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

    /**
     * @param bool $condition
     * @param string $conditionLabel
     * @param int|null $errorCode
     * @return $this
     * @throws FileHelper_Exception
     */
    private function requireTrue(bool $condition, string $conditionLabel, ?int $errorCode=null) : self
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
    public function requireExists(?int $errorCode=null) : self
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
    public function requireReadable(?int $errorCode=null) : self
    {
        $this->requireExists($errorCode);

        return $this->requireTrue(
            $this->isReadable(),
            'is not readable',
            FileHelper::ERROR_FILE_NOT_READABLE
        );
    }

    /**
     * @param int|null $errorCode
     * @return $this
     * @throws FileHelper_Exception
     */
    public function requireWritable(?int $errorCode=null) : self
    {
        return $this->requireTrue(
            $this->isWritable(),
            'is not writable',
            FileHelper::ERROR_PATH_NOT_WRITABLE
        );
    }

    /**
     * @return FileInfo
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_PATH_IS_NOT_A_FILE
     */
    public function requireIsFile() : FileInfo
    {
        if($this instanceof FileInfo)
        {
            return $this;
        }

        throw new FileHelper_Exception(
            'Target path is not a file',
            sprintf(
                'Path: [%s].',
                $this->path
            ),
            FileHelper::ERROR_PATH_IS_NOT_A_FILE
        );
    }

    /**
     * @return FolderInfo
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_PATH_IS_NOT_A_FOLDER
     */
    public function requireIsFolder() : FolderInfo
    {
        if($this instanceof FolderInfo)
        {
            return $this;
        }

        throw new FileHelper_Exception(
            'Target path is not a folder',
            sprintf(
                'Path: [%s].',
                $this->path
            ),
            FileHelper::ERROR_PATH_IS_NOT_A_FOLDER
        );
    }

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return string
     */
    public static function type2string($path) : string
    {
        if($path instanceof PathInfoInterface)
        {
            return $path->getPath();
        }

        if($path instanceof SplFileInfo)
        {
            return $path->getPathname();
        }

        return $path;
    }

    /**
     * Resolves the type of the target path: file or folder.
     *
     * NOTE: Requires the file or folder to exist in the
     * file system, and will throw an exception otherwise.
     *
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return PathInfoInterface
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_PATH_INVALID
     */
    public static function resolveType($path) : PathInfoInterface
    {
        if($path instanceof PathInfoInterface)
        {
            return $path;
        }

        $path = self::type2string($path);

        if(FolderInfo::is_dir($path))
        {
            return FolderInfo::factory($path);
        }

        if(FileInfo::is_file($path))
        {
            return FileInfo::factory($path);
        }

        throw new FileHelper_Exception(
            'Invalid file or folder path.',
            sprintf(
                'Target path: [%s].',
                $path
            ),
            FileHelper::ERROR_PATH_INVALID
        );
    }

    /**
     * Stores an arbitrary value in this object, which can
     * be retrieved again with {@see AbstractPathInfo::getRuntimeProperty()}.
     *
     * These properties have no functionality beyond offering
     * a way to store custom data.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setRuntimeProperty(string $name, $value) : self
    {
        $this->runtimeProperties[$name] = $value;
        return $this;
    }

    /**
     * Retrieves a previously set property, if any.
     *
     * @param string $name
     * @return mixed|null The stored value, or null if it does not exist (or has a null value).
     */
    public function getRuntimeProperty(string $name)
    {
        return $this->runtimeProperties[$name] ?? null;
    }

    public function __toString() : string
    {
        return $this->getPath();
    }

    /**
     * Retrieves the last modified date for the specified file or folder.
     *
     * Note: If the target does not exist, returns null.
     *
     * @return DateTime|NULL
     */
    public function getModifiedDate() : ?DateTime
    {
        $time = filemtime($this->getPath());
        if($time === false) {
            return null;
        }

        $date = new DateTime();
        $date->setTimestamp($time);
        return $date;
    }
}
