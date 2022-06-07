<?php

declare(strict_types=1);

namespace testsuites\FileHelperTests;

use AppUtils\FileHelper\FolderInfo;
use PHPUnit\Framework\TestCase;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

final class FileFinderTest extends TestCase
{
    // region: _Tests

    public function test_findFiles_default() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);

        $files = $finder->getAll();

        $expected = array(
            $this->assetsFolder . '/.extension',
            $this->assetsFolder . '/README.txt',
            $this->assetsFolder . '/test-png.png'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_recursive() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);

        $finder->makeRecursive();

        $files = $finder->getAll();

        $expected = array(
            $this->assetsFolder . '/.extension',
            $this->assetsFolder . '/README.txt',
            $this->assetsFolder . '/test-png.png',
            $this->assetsFolder . '/Subfolder/script.php',
            $this->assetsFolder . '/Classmap/Class.php',
            $this->assetsFolder . '/Classmap/Class/Subclass.php',
            $this->assetsFolder . '/Classmap/Class/Subclass/Subsubclass.php'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_relative() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);

        $finder->makeRecursive();
        $finder->setPathmodeRelative();

        $files = $finder->getAll();

        $expected = array(
            '.extension',
            'README.txt',
            'test-png.png',
            'Subfolder/script.php',
            'Classmap/Class.php',
            'Classmap/Class/Subclass.php',
            'Classmap/Class/Subclass/Subsubclass.php'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_stripExtensions() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);

        $finder->makeRecursive();
        $finder->setPathmodeRelative();
        $finder->stripExtensions();

        $files = $finder->getAll();

        $expected = array(
            'README',
            'test-png',
            'Subfolder/script',
            'Classmap/Class',
            'Classmap/Class/Subclass',
            'Classmap/Class/Subclass/Subsubclass'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_excludeExtensions() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);

        $finder->setPathmodeRelative();
        $finder->excludeExtensions(array('txt'));

        $files = $finder->getAll();

        $expected = array(
            '.extension',
            'test-png.png',
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_includeExtensions() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);

        $finder->setPathmodeRelative();
        $finder->includeExtensions(array('txt'));

        $files = $finder->getAll();

        $expected = array(
            'README.txt',
        );

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_pathSeparator() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);

        $finder->makeRecursive();
        $finder->stripExtensions();
        $finder->setSlashReplacement('-');
        $finder->setPathmodeRelative();
        $finder->includeExtensions(array('php'));

        $files = $finder->getAll();

        $expected = array(
            'Subfolder-script',
            'Classmap-Class',
            'Classmap-Class-Subclass',
            'Classmap-Class-Subclass-Subsubclass'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_getPHPClassNames() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);

        $finder->makeRecursive();

        $files = $finder->getPHPClassNames();

        $expected = array(
            'Subfolder_script',
            'Classmap_Class',
            'Classmap_Class_Subclass',
            'Classmap_Class_Subclass_Subsubclass'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_pathNotExists() : void
    {
        $this->expectException(FileHelper_Exception::class);

        FileHelper::createFileFinder(md5('/path/that/does/not/exist'));
    }

    // endregion

    // region: Support methods

    protected FolderInfo $assetsFolder;

    protected function setUp() : void
    {
        $folder = FileHelper::getFolderInfo(__DIR__ . '/../../assets/FileHelper/FileFinder');

        $folder->requireExists()->requireIsFolder();

        $this->assetsFolder = $folder;
    }

    // endregion
}
