<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper;
use AppUtils\Interface_Optionable;
use AppUtils\Traits_Optionable;
use DirectoryIterator;
use SplFileInfo;

class FolderFinder implements Interface_Optionable
{
    use Traits_Optionable;

    public const OPTION_RECURSIVE = 'recursive';
    public const OPTION_ABSOLUTE_PATH = 'absolute-path';

    private FolderInfo $folder;

    /**
     * @var FolderInfo[]
     */
    private array $folders;

    /**
     * @param string|SplFileInfo|FolderInfo $path
     */
    public function __construct($path)
    {
        if($path instanceof FolderInfo)
        {
            $this->folder = $path;
        }
        else
        {
            $this->folder = FileHelper::getFolderInfo(FileHelper::getPathInfo($path)->getPath());
        }
    }

    public function getDefaultOptions() : array
    {
        return array(
            self::OPTION_RECURSIVE => false,
            self::OPTION_ABSOLUTE_PATH => false,
        );
    }

    public function makeRecursive(bool $recursive=true) : self
    {
        return $this->setOption(self::OPTION_RECURSIVE, $recursive);
    }

    public function setPathModeAbsolute() : self
    {
        return $this->setOption(self::OPTION_ABSOLUTE_PATH, true);
    }

    public function setPathModeRelative(): self
    {
        return $this->setOption(self::OPTION_ABSOLUTE_PATH, false);
    }

    private function requireValid() : void
    {
        $this->folder
            ->requireExists(FileHelper::ERROR_FIND_SUBFOLDERS_FOLDER_DOES_NOT_EXIST);
    }

    /**
     * @return string[]
     */
    public function getPaths() : array
    {
        $this->findFolders();

        $result = array();

        foreach($this->folders as $folder)
        {
            $result[] = $this->resolvePath($folder);
        }

        sort($result);

        return $result;
    }

    /**
     * @return FolderInfo[]
     */
    public function getFolderInfos() : array
    {
        $this->findFolders();

        return $this->folders;
    }

    private function resolvePath(FolderInfo $folder) : string
    {
        if(!$this->isPathModeAbsolute())
        {
            return $folder->getRelativeTo($this->folder);
        }

        return $folder->getPath();
    }

    private function findFolders() : void
    {
        $this->requireValid();

        $this->folders = array();

        $this->scanFolder($this->folder);
    }

    private function scanFolder(FolderInfo $folder) : void
    {
        $d = new DirectoryIterator($folder->getPath());

        foreach($d as $item)
        {
            if($item->isDir() && !$item->isDot())
            {
                $this->processFolder(FileHelper::getFolderInfo($item->getPathname()));
            }
        }
    }

    public function isPathModeAbsolute() : bool
    {
        return $this->getBoolOption(self::OPTION_ABSOLUTE_PATH);
    }

    public function isRecursive() : bool
    {
        return $this->getBoolOption(self::OPTION_RECURSIVE);
    }

    private function processFolder(FolderInfo $folder) : void
    {
        $this->folders[] = $folder;

        if($this->isRecursive())
        {
            $this->scanFolder($folder);
        }
    }
}
