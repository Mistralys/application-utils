<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use DirectoryIterator;

class FolderTree
{
    /**
     * Deletes a folder tree with all files therein, including
     * the specified folder itself.
     *
     * @param string $rootFolder
     * @return bool
     */
    public static function delete(string $rootFolder) : bool
    {
        if(!file_exists($rootFolder))
        {
            return true;
        }

        $d = new DirectoryIterator($rootFolder);

        foreach ($d as $item)
        {
            if(self::processDeleteItem($item) === false)
            {
                return false;
            }
        }

        return rmdir($rootFolder);
    }

    /**
     * @param DirectoryIterator $item
     * @return bool
     */
    private static function processDeleteItem(DirectoryIterator $item) : bool
    {
        if ($item->isDot())
        {
            return true;
        }

        $itemPath = $item->getRealPath();

        if (!is_readable($itemPath))
        {
            return false;
        }

        if ($item->isDir())
        {
            return self::delete($itemPath);
        }

        if ($item->isFile())
        {
            try
            {
                FileHelper::deleteFile($itemPath);
            }
            catch (FileHelper_Exception $e)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Copies a folder tree to the target folder.
     *
     * @param string $source
     * @param string $target
     * @throws FileHelper_Exception
     */
    public static function copy(string $source, string $target) : void
    {
        FileHelper::createFolder($target);

        $d = new DirectoryIterator($source);
        foreach ($d as $item)
        {
            self::processCopyItem($item, $target);
        }
    }

    /**
     * @param DirectoryIterator $item
     * @param string $target
     * @return void
     * @throws FileHelper_Exception
     */
    private static function processCopyItem(DirectoryIterator $item, string $target) : void
    {
        if ($item->isDot())
        {
            return;
        }

        $itemPath = FileHelper::requireFileReadable($item->getPathname());
        $baseName = basename($itemPath);

        if ($item->isDir())
        {
            self::copy($itemPath, $target . '/' . $baseName);
        }
        else if($item->isFile())
        {
            FileHelper::copyFile($itemPath, $target . '/' . $baseName);
        }
    }
}
