<?php

declare(strict_types=1);

namespace testsuites\FileHelperTests;

use AppUtils\ConvertHelper;
use AppUtils\FileHelper\AbstractPathInfo;
use DirectoryIterator;
use TestClasses\FileHelperTestCase;

final class ResolvePathTypeTest extends FileHelperTestCase
{
    /**
     * Sanity check to ensure the prerequisites are
     * everything as expected. This was added because
     * during the Travis tests, some folders were
     * detected as files.
     */
    public function test_controlIteratorBehavior_AssetPath() : void
    {
        $folderPath = __DIR__.'/../../assets/FileHelper/FolderTree';

        $iterator = new DirectoryIterator($folderPath);

        $this->assertTrue(
            $iterator->isDir(),
            sprintf(
                'Iterator is not a folder: '.PHP_EOL.
                '[%s]'.PHP_EOL.
                'Folder is readable: '.PHP_EOL.
                '[%s]',
                $iterator->getPath(),
                ConvertHelper::boolStrict2string(is_readable($folderPath))
            )
        );
    }

    public function test_controlIteratorBehavior_Constant() : void
    {
        $folderPath = __DIR__;

        $iterator = new DirectoryIterator($folderPath);

        $this->assertTrue(
            $iterator->isDir(),
            sprintf(
                'Iterator is not a folder: '.PHP_EOL.
                '[%s]'.PHP_EOL.
                'Folder is readable: '.PHP_EOL.
                '[%s]',
                $iterator->getPath(),
                ConvertHelper::boolStrict2string(is_readable($folderPath))
            )
        );
    }

    public function test_resolveType_FolderString() : void
    {
        $path = __DIR__;
        $this->assertTrue(is_readable($path));
        $this->assertTrue(is_dir($path));

        $stringFolder = AbstractPathInfo::resolveType($path);

        $this->assertTrue(
            $stringFolder->isFolder(),
            'Must be detected as a folder: ['.$stringFolder.']'
        );
    }

    public function test_resolveType_FolderIterator() : void
    {
        $path = __DIR__;
        $this->assertTrue(is_readable($path));
        $this->assertTrue(is_dir($path));

        $iterator = new DirectoryIterator($path);
        $this->assertSame($path, $iterator->getPath());
        $this->assertSame('dir', $iterator->getType());
        $this->assertTrue($iterator->isDir());

        $iteratorFolder = AbstractPathInfo::resolveType($iterator);

        $this->assertTrue(
            $iteratorFolder->isFolder(),
            'Must be detected as a folder: ['.$iteratorFolder.']'
        );
    }

    /**
     * When passing an existing path info instance
     * to {@see AbstractPathInfo::resolveType()}, this
     * must not be modified and directly passed through.
     */
    public function test_instancePassThrough() : void
    {
        $stringFolder = AbstractPathInfo::resolveType('/path/to/folder');

        $this->assertSame($stringFolder, AbstractPathInfo::resolveType($stringFolder));
    }

    public function test_resolveType_File() : void
    {
        $path = __FILE__;
        $this->assertTrue(is_readable($path));
        $this->assertTrue(is_file($path));

        $stringFile = AbstractPathInfo::resolveType($path);

        $this->assertTrue(
            $stringFile->isFile(),
            'Should be a file: ['.$stringFile.']'
        );
    }
}
