<?php

declare(strict_types=1);

namespace testsuites\FileHelperTests;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\IndeterminatePath;
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
            $info = FolderInfo::factory($folderName);

            $message = 'Folder: ['.$folderName.']';

            $this->assertTrue($info->isFolder(), $message);
            $this->assertFalse($info->isFile(), $message);
            $this->assertTrue(FolderInfo::is_dir($folderName), $message);

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
        $this->expectExceptionCode(FileHelper::ERROR_PATH_INVALID);

        $this->assertInstanceOf(FolderInfo::class, FileHelper::getPathInfo(''));
    }

    public function test_dotPath() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_INVALID);

        FileHelper::getPathInfo('.');
    }

    public function test_dotDotPath() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_INVALID);

        $this->assertInstanceOf(FolderInfo::class, FileHelper::getPathInfo('..'));
    }

    public function test_endsInDot() : void
    {
        $this->assertInstanceOf(
            IndeterminatePath::class,
            FileHelper::getPathInfo('name.')
        );
    }

    public function test_isWithinPath() : void
    {
        $baseFolder = $this->assetsFolder;

        $this->assertFileExists($baseFolder);

        $tests = array(
            array(
                'label' => 'File within folder',
                'source' => $baseFolder.'/FolderTree/SubFolderA',
                'target' => $baseFolder.'/FolderTree/SubFolderA/readme.md',
                'expected' => true
            ),
            array(
                'label' => 'File within subfolder',
                'source' => $baseFolder.'/FolderTree/SubFolderB',
                'target' => $baseFolder.'/FolderTree/SubFolderB/SubSubFolder/SubSubSubFolder/readme.md',
                'expected' => true
            ),
            array(
                'label' => 'Subfolder within folder',
                'source' => $baseFolder.'/FolderTree/SubFolderB',
                'target' => $baseFolder.'/FolderTree/SubFolderB/SubSubFolder',
                'expected' => true
            ),
            array(
                'label' => 'Parent folder',
                'source' => $baseFolder.'/FolderTree/SubFolderB',
                'target' => $baseFolder,
                'expected' => false
            ),
            array(
                'label' => 'Other folder',
                'source' => $baseFolder,
                'target' => __DIR__,
                'expected' => false
            ),
            array(
                'label' => 'File within file',
                'source' => $baseFolder.'/copy-file.txt',
                'target' => $baseFolder.'/42-bytes.txt',
                'expected' => true
            ),
            array(
                'label' => 'Subfolder file within file',
                'source' => $baseFolder.'/42-bytes.txt',
                'target' => $baseFolder.'/FolderTree/SubFolderA/readme.md',
                'expected' => true
            ),
            array(
                'label' => 'Subfolder within file',
                'source' => $baseFolder.'/42-bytes.txt',
                'target' => $baseFolder.'/FolderTree/',
                'expected' => true
            ),
            array(
                'label' => 'Parent folder within file',
                'source' => $baseFolder.'/FolderTree/SubFolderA/readme.md',
                'target' => $baseFolder.'/FolderTree',
                'expected' => false
            ),
            array(
                'label' => 'Relative path shenanigans',
                'source' => $baseFolder.'/FolderTree/SubFolderA/readme.md',
                'target' => $baseFolder.'/FolderTree/SubFolderB/SubSubFolder/../../SubFolderA/readme.md',
                'expected' => true
            )
        );

        foreach($tests as $test)
        {
            $this->assertSame(
                $test['expected'],
                FileHelper::getPathInfo($test['target'])->isWithinPath($test['source']),
                'Checked test "'.$test['label'].'": '.PHP_EOL.
                '- Source: '.$test['source'].PHP_EOL.
                '- Target: '.$test['target']
            );
        }
    }
}
