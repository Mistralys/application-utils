<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use DirectoryIterator;

class FolderFinder
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param string|DirectoryIterator $targetFolder
     * @param array<string,bool|string> $options
     * @return string[]
     * @throws FileHelper_Exception
     */
    public static function getSubFolders($targetFolder, array $options = array()) : array
    {
        $folder = FileHelper::getPathInfo($targetFolder)
            ->requireExists(FileHelper::ERROR_FIND_SUBFOLDERS_FOLDER_DOES_NOT_EXIST)
            ->requireIsFolder();

        $options = array_merge(
            array(
                'recursive' => false,
                'absolute-path' => false
            ),
            $options
        );

        $result = array();

        $d = new DirectoryIterator($folder->getPath());

        foreach($d as $item)
        {
            if($item->isDir() && !$item->isDot())
            {
                $name = $item->getFilename();

                if(!$options['absolute-path']) {
                    $result[] = $name;
                } else {
                    $result[] = $targetFolder.'/'.$name;
                }

                if(!$options['recursive'])
                {
                    continue;
                }

                $subs = self::getSubFolders($targetFolder.'/'.$name, $options);
                foreach($subs as $sub)
                {
                    $relative = $name.'/'.$sub;

                    if(!$options['absolute-path']) {
                        $result[] = $relative;
                    } else {
                        $result[] = $targetFolder.'/'.$relative;
                    }
                }
            }
        }

        return $result;
    }
}
