<?php

declare(strict_types=1);

namespace testsuites\FileHelperTests;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper_Exception;
use TestClasses\FileHelperTestCase;

class PathInfoTest extends FileHelperTestCase
{
    public function test_notFolderException() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_IS_NOT_A_FOLDER);

        FileHelper::getFolderInfo('not-a-folder.ext');
    }

    public function test_folderIsFolder() : void
    {
        foreach($this->testFolderNames as $folderName)
        {
            $info = FileHelper::getPathInfo($folderName);

            $this->assertInstanceOf(FolderInfo::class, $info);
            $this->assertTrue($info->isFolder());
            $this->assertFalse($info->isFile());
            $this->assertTrue(FolderInfo::is_dir($folderName));

            FileHelper::getFolderInfo($folderName);
            $this->addToAssertionCount(1);
        }
    }

    public function test_fileIsNotFolder() : void
    {
        foreach($this->testFileNames as $fileName)
        {
            $info = FileHelper::getPathInfo($fileName);

            $this->assertInstanceOf(FileInfo::class, $info);
            $this->assertFalse($info->isFolder());
            $this->assertTrue($info->isFile());
            $this->assertFalse(FolderInfo::is_dir($fileName));
        }
    }

    public function test_emptyPath() : void
    {
        $this->expectException(FileHelper_Exception::class);

        $this->assertInstanceOf(FolderInfo::class, FileHelper::getPathInfo(''));
    }

    public function test_dotPath() : void
    {
        $this->expectException(FileHelper_Exception::class);

        $this->assertInstanceOf(FolderInfo::class, FileHelper::getPathInfo('.'));
    }

    public function test_dotDotPath() : void
    {
        $this->expectException(FileHelper_Exception::class);

        $this->assertInstanceOf(FolderInfo::class, FileHelper::getPathInfo('..'));
    }

    public function test_endsInDot() : void
    {
        $this->assertInstanceOf(FolderInfo::class, FileHelper::getPathInfo('name.'));
    }
}
