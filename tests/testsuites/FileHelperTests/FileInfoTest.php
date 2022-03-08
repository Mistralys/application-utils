<?php

declare(strict_types=1);

namespace FileHelperTests;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use TestClasses\FileHelperTestCase;

class FileInfoTest extends FileHelperTestCase
{
    public function test_notFileException() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_IS_NOT_A_FILE);

        FileHelper::getFileInfo('/not/a/file');
    }

    public function test_getExtensionFileExists() : void
    {
        $info = FileInfo::factory($this->assetsFolder.'/'.self::CASE_FILE_LOWER);

        $this->assertTrue($info->isFile());
        $this->assertSame(self::CASE_EXTENSION_LOWER, $info->getExtension());
        $this->assertSame(self::CASE_FILE_LOWER, $info->getName());
        $this->assertSame(self::CASE_BASE_LOWER, $info->removeExtension());
    }

    public function test_getExtensionFileNotExists() : void
    {
        $info = FileInfo::factory('unknown-file.ext');

        $this->assertTrue($info->isFile());
        $this->assertSame('ext', $info->getExtension());
        $this->assertSame('unknown-file.ext', $info->getName());
        $this->assertSame('unknown-file', $info->removeExtension());
    }

    public function test_getExtensionOnlyExtension() : void
    {
        $info = FileInfo::factory('.htaccess');

        $this->assertTrue($info->isFile());
        $this->assertSame('htaccess', $info->getExtension());
        $this->assertSame('.htaccess', $info->getName());
        $this->assertSame('', $info->removeExtension());
    }

    public function test_getExtensionCase() : void
    {
        $info = FileInfo::factory('file.EXTENSION');

        $this->assertSame('EXTENSION', $info->getExtension(false));
        $this->assertSame('extension', $info->getExtension());
    }

    public function test_folderPath() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_IS_NOT_A_FILE);

        FileInfo::factory('path/to/folder');
    }

    public function test_folderIsNotFile() : void
    {
        foreach($this->testFolderNames as $folderName)
        {
            $info = FileHelper::getPathInfo($folderName);

            $this->assertNotInstanceOf(FileInfo::class, $info);
            $this->assertTrue($info->isFolder());
            $this->assertFalse($info->isFile());
            $this->assertFalse(FileInfo::is_file($folderName));
        }
    }

    public function test_fileIsFile() : void
    {
        foreach($this->testFileNames as $fileName)
        {
            $info = FileHelper::getPathInfo($fileName);

            $this->assertInstanceOf(FileInfo::class, $info);

            $this->assertFalse($info->isFolder(), $fileName);
            $this->assertTrue($info->isFile());
            $this->assertTrue(FileInfo::is_file($fileName));

            // Getting a file info explicitly must not trigger an exception
            FileHelper::getFileInfo($fileName);
            $this->addToAssertionCount(1);
        }
    }
}
