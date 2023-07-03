<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * An indeterminate path is a special case, where
 * the target file or folder does not exist on disk
 * yet, and it does not have an extension, so it
 * can be either a path or a file without extension.
 *
 *
 */
class IndeterminatePath extends AbstractPathInfo
{
    public const ERROR_INVALID_CONVERSION_TYPE = 115501;

    public const CONVERT_TYPE_FILE = 'file';
    public const CONVERT_TYPE_FOLDER = 'folder';

    public function getExtension(bool $lowercase = true) : string
    {
        return '';
    }

    public function getFolderPath() : string
    {
        return $this->getPath();
    }

    public function delete() : self
    {
        return $this;
    }

    public function getSize(): int
    {
        return 0;
    }

    public function convertToFile() : FileInfo
    {
        // Doing this manually, as FileHelper::saveFile()
        // checks if the target is a file, which will fail.
        if(file_put_contents($this->getPath(), '') === false) {
            throw new FileHelper_Exception(
                'Cannot create file',
                '',
                FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
            );
        }

        return FileInfo::factory($this->getPath());
    }

    public function convertToFolder() : FolderInfo
    {
        $path = $this->getPath();

        // Doing this manually, as FileHelper::createFolder()
        // checks if the target is a folder, which will fail.
        if(!mkdir($path, 0777, true) && !is_dir($path))
        {
            throw new FileHelper_Exception(
                'Cannot create folder',
                '',
                FileHelper::ERROR_CANNOT_CREATE_FOLDER
            );
        }

        return FolderInfo::factory($path);
    }

    public function convertTo(string $type) : PathInfoInterface
    {
        if($type === self::CONVERT_TYPE_FILE) {
            return $this->convertToFile();
        }

        if($type === self::CONVERT_TYPE_FOLDER) {
            return $this->convertToFolder();
        }

        throw new FileHelper_Exception(
            'Invalid conversion type.',
            sprintf(
                'The specified type [%s] does not exist.',
                $type
            ),
            self::ERROR_INVALID_CONVERSION_TYPE
        );
    }
}
