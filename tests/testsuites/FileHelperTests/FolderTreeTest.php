<?php

declare(strict_types=1);

namespace FileHelperTests;

use AppUtils\FileHelper;
use TestClasses\FileHelperTestCase;

class FolderTreeTest extends FileHelperTestCase
{
    public function test_getSubFolders() : void
    {
        $folders = FileHelper::getSubfolders($this->assetsFolder.'/FolderTree');

        $this->assertSame(
            array(
                'SubFolderA',
                'SubFolderB'
            ),
            $folders
        );
    }

    public function test_getSubFoldersRecursive() : void
    {
        $folders = FileHelper::getSubfolders(
            $this->assetsFolder.'/FolderTree',
            array(
                'recursive' => true
            )
        );

        $this->assertSame(
            array(
                'SubFolderA',
                'SubFolderB',
                'SubFolderB/SubSubFolder',
                'SubFolderB/SubSubFolder/SubSubSubFolder'
            ),
            $folders
        );
    }

    public function test_getSubFolders_folderFinder() : void
    {
        $folders = FileHelper::getFolderInfo($this->assetsFolder.'/FolderTree')
            ->createFolderFinder()
            ->makeRecursive()
            ->getPaths();

        $this->assertSame(
            array(
                'SubFolderA',
                'SubFolderB',
                'SubFolderB/SubSubFolder',
                'SubFolderB/SubSubFolder/SubSubSubFolder'
            ),
            $folders
        );
    }
}
