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
     * @param string|PathInfoInterface|DirectoryIterator $rootFolder
     * @return bool
     * @throws FileHelper_Exception
     */
    public static function delete($rootFolder) : bool
    {
        $info = FileHelper::getFolderInfo($rootFolder);

        if(!$info->exists())
        {
            return true;
        }

        $d = $info->getIterator();

        foreach ($d as $item)
        {
            if(self::processDeleteItem($item) === false)
            {
                return false;
            }
        }

        try
        {
            $info->delete();
            return true;
        }
        catch (FileHelper_Exception $e)
        {

        }

        return false;
    }

    /**
     * @param DirectoryIterator $item
     * @return bool
     * @throws FileHelper_Exception
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
     * @param string|PathInfoInterface|DirectoryIterator $source
     * @param string|PathInfoInterface|DirectoryIterator $target
     * @throws FileHelper_Exception
     */
    public static function copy($source, $target) : void
    {
        $target = FileHelper::createFolder($target);

        $d =  FileHelper::getPathInfo($source)->requireIsFolder()->getIterator();

        foreach ($d as $item)
        {
            if($item->isDot())
            {
                continue;
            }

            self::processCopyItem(FileHelper::getPathInfo($item), $target);
        }
    }

    /**
     * @param PathInfoInterface $item
     * @param FolderInfo $target
     * @return void
     * @throws FileHelper_Exception
     */
    private static function processCopyItem(PathInfoInterface $item, FolderInfo $target) : void
    {
        $item->requireReadable();

        if ($item->isFolder())
        {
            self::copy($item, $target . '/' . $item->getName());
        }
        else if($item->isFile())
        {
            $item
                ->requireIsFile()
                ->copyTo($target.'/'.$item->getName());
        }
    }
}
