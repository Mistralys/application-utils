<?php

declare(strict_types=1);

namespace FileHelperTests;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use TestClasses\FileHelperTestCase;

class FolderTreeTest extends FileHelperTestCase
{
    public function test_getSubFolders() : void
    {
        $folders = FileHelper::getSubfolders($this->testFolder);

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
            $this->testFolder,
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
        $folders = FileHelper::getFolderInfo($this->testFolder)
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

    public function test_deleteTree() : void
    {
        $baseFolder = FolderInfo::factory($this->testFolder);

        $folderA = $baseFolder->createSubFolder('DeleteRoot');
        $folderA->createSubFolder('DeleteSubAEmpty');
        $folderA->createSubFolder('DeleteSubBNonEmpty')
            ->saveFile('DeleteFile.txt', 'Some content');

        FileHelper::deleteTree($folderA);

        $this->assertDirectoryDoesNotExist((string)$folderA);
    }

    public function test_copyTree() : void
    {
        $sourceFolder = $this->testFolder.'/SubFolderB';
        $targetFolder = $this->testFolder.'/SubFolderBCopy';

        FileHelper::copyTree($sourceFolder, $targetFolder);

        $this->assertDirectoryExists($targetFolder);
        $this->assertFileExists($targetFolder.'/SubSubFolder/SubSubSubFolder/readme.md');

        FileHelper::deleteTree($targetFolder);
    }

    private string $testFolder;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testFolder = __DIR__.'/../../assets/FileHelper/FolderTree';
    }
}
