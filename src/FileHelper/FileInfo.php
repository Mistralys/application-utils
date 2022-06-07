<?php
/**
 * File containing the class {@see \AppUtils\FileHelper\FileInfo}.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\FileInfo
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo\FileSender;
use AppUtils\FileHelper\FileInfo\LineReader;
use AppUtils\FileHelper_Exception;
use DirectoryIterator;

/**
 * Specialized class used to access information on a file path,
 * and do file-related operations: reading contents, deleting
 * or copying and the like.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FileInfo extends AbstractPathInfo
{
    /**
     * @var array<string,FileInfo>
     */
    private static $infoCache = array();

    /**
     * @param string|PathInfoInterface|DirectoryIterator $path
     * @return FileInfo
     * @throws FileHelper_Exception
     */
    public static function factory($path) : FileInfo
    {
        $pathString = AbstractPathInfo::type2string($path);

        if(!isset(self::$infoCache[$pathString]))
        {
            self::$infoCache[$pathString] = new FileInfo($pathString);
        }

        return self::$infoCache[$pathString];
    }

    /**
     * Clears the file cache that keeps track of any files
     * created via {@see FileInfo::factory()} for performance
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
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_PATH_IS_NOT_A_FILE
     */
    public function __construct(string $path)
    {
        parent::__construct($path);

        if(!self::is_file($this->path))
        {
            throw new FileHelper_Exception(
                'Not a file path',
                sprintf('The path is not a file: [%s].', $this->path),
                FileHelper::ERROR_PATH_IS_NOT_A_FILE
            );
        }
    }

    public static function is_file(string $path) : bool
    {
        $path = trim($path);

        if(empty($path))
        {
            return false;
        }

        return is_file($path) || pathinfo($path, PATHINFO_EXTENSION) !== '';
    }

    public function removeExtension(bool $keepPath=false) : string
    {
        if(!$keepPath)
        {
            return pathinfo($this->getName(), PATHINFO_FILENAME);
        }

        $parts = explode('/', $this->path);

        $file = pathinfo(array_pop($parts), PATHINFO_FILENAME);

        $parts[] = $file;

        return implode('/', $parts);
    }

    public function getExtension(bool $lowercase=true) : string
    {
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);

        if($lowercase)
        {
            $ext = mb_strtolower($ext);
        }

        return $ext;
    }

    /**
     * @return $this
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_DELETE_FILE
     */
    public function delete() : FileInfo
    {
        if(!$this->exists())
        {
            return $this;
        }

        if(unlink($this->path))
        {
            return $this;
        }

        throw new FileHelper_Exception(
            sprintf(
                'Cannot delete file [%s].',
                $this->getName()
            ),
            sprintf(
                'The file [%s] cannot be deleted.',
                $this->getPath()
            ),
            FileHelper::ERROR_CANNOT_DELETE_FILE
        );
    }

    /**
     * @param string $targetPath
     * @return FileInfo
     * @throws FileHelper_Exception
     */
    public function copyTo(string $targetPath) : FileInfo
    {
        $this->checkCopyPrerequisites($targetPath);

        if(copy($this->path, $targetPath))
        {
            return self::factory($targetPath);
        }

        throw new FileHelper_Exception(
            sprintf(
                'Cannot copy file [%s].',
                $this->getName()
            ),
            sprintf(
                'The file [%s] could not be copied from [%s] to [%s].',
                $this->getName(),
                $this->path,
                $targetPath
            ),
            FileHelper::ERROR_CANNOT_COPY_FILE
        );
    }

    /**
     * @param string $targetPath
     * @return void
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_SOURCE_FILE_NOT_FOUND
     * @see FileHelper::ERROR_SOURCE_FILE_NOT_READABLE
     * @see FileHelper::ERROR_TARGET_COPY_FOLDER_NOT_WRITABLE
     */
    private function checkCopyPrerequisites(string $targetPath) : void
    {
        $this->requireExists(FileHelper::ERROR_SOURCE_FILE_NOT_FOUND);
        $this->requireReadable(FileHelper::ERROR_SOURCE_FILE_NOT_READABLE);

        FolderInfo::factory(dirname($targetPath))
            ->create()
            ->requireWritable(FileHelper::ERROR_TARGET_COPY_FOLDER_NOT_WRITABLE);
    }

    /**
     * @var LineReader|NULL
     */
    private ?LineReader $lineReader = null;

    /**
     * Gets an instance of the line reader, which can
     * read contents of the file, line by line.
     *
     * @return LineReader
     */
    public function getLineReader() : LineReader
    {
        if(!isset($this->lineReader))
        {
            $this->lineReader = new LineReader($this);
        }

        return $this->lineReader;
    }

    /**
     * @return string
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_READ_FILE_CONTENTS
     */
    public function getContents() : string
    {
        $this->requireExists();

        $result = file_get_contents($this->getPath());

        if($result !== false) {
            return $result;
        }

        throw new FileHelper_Exception(
            sprintf('Cannot read contents of file [%s].', $this->getName()),
            sprintf(
                'Tried opening file for reading at: [%s].',
                $this->getPath()
            ),
            FileHelper::ERROR_CANNOT_READ_FILE_CONTENTS
        );
    }

    /**
     * @param string $content
     * @return $this
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
     */
    public function putContents(string $content) : FileInfo
    {
        if($this->exists())
        {
            $this->requireWritable();
        }
        else
        {
            FolderInfo::factory(dirname($this->path))
                ->create()
                ->requireWritable();
        }

        if(file_put_contents($this->path, $content) !== false)
        {
            return $this;
        }

        throw new FileHelper_Exception(
            sprintf('Cannot save file: writing content to the file [%s] failed.', $this->getName()),
            sprintf(
                'Tried saving content to file in path [%s].',
                $this->getPath()
            ),
            FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
        );
    }

    public function getDownloader() : FileSender
    {
        return new FileSender($this);
    }
}
