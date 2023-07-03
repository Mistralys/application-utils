<?php

declare(strict_types=1);

namespace FileHelperTests;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use TestClasses\FileHelperTestCase;

class FolderInfoTest extends FileHelperTestCase
{
    public function test_isFolderExists() : void
    {
        $info = FolderInfo::factory($this->assetsFolder.'/FileFinder');

        $this->assertTrue($info->exists());
        $this->assertTrue($info->isFolder());
        $this->assertSame('FileFinder', $info->getName());
    }

    public function test_isFolderNotExists() : void
    {
        $info = FolderInfo::factory('UnknownFolder');

        $this->assertTrue($info->isFolder());
        $this->assertFalse($info->exists());
        $this->assertSame('UnknownFolder', $info->getName());
    }

    public function test_filePath() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_IS_NOT_A_FOLDER);

        FolderInfo::factory('UnknownFolder/File.ext');
    }

    public function test_emptyFolder() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_INVALID);

        FolderInfo::factory('');
    }

    public function test_is_dir() : void
    {
        $this->assertFalse(FolderInfo::is_dir('file-name.ext'));
        $this->assertFalse(FolderInfo::is_dir('path/to/folder/file-name.ext'));
        $this->assertFalse(FolderInfo::is_dir(''));
        $this->assertTrue(FolderInfo::is_dir('path/to/folder/'));
        $this->assertTrue(FolderInfo::is_dir('path/'));
        $this->assertTrue(FolderInfo::is_dir('path./'));
        $this->assertFalse(FolderInfo::is_dir('.'));
        $this->assertFalse(FolderInfo::is_dir('..'));
        $this->assertFalse(FolderInfo::is_dir('./'));
        $this->assertFalse(FolderInfo::is_dir('../'));
    }

    public function test_saveJSONFile() : void
    {
        $info = FolderInfo::factory(__DIR__.'/../../assets/FileHelper/PathInfo');

        $jsonFile = $info->saveJSONFile(array('foo' => 'bar'), 'TestJSON.json');

        $this->assertFileExists($jsonFile->getPath());

        FileHelper::deleteFile($jsonFile);
    }

    public function test_getSize() : void
    {
        $info = FolderInfo::factory(__DIR__.'/../../assets/FileHelper/PathInfo/FolderWithFiles');

        $this->assertSame(14, $info->getSize());
    }
}
