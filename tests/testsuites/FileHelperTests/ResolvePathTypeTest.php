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

        $this->debugIterator($folderPath);
    }

    public function test_controlIteratorBehavior_Constant() : void
    {
        $folderPath = __DIR__;

        $this->debugIterator($folderPath);
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

    public function debugIterator(string $folderPath) : void
    {
        $iterator = new DirectoryIterator($folderPath);
        $iterator->rewind();

        $this->assertTrue(
            $iterator->isDir(),
            sprintf(
                'Iterator not detected as a folder. '.PHP_EOL.
                '%s',
                print_r(array(
                    'target path' => $folderPath,
                    'functions' => array(
                        'is_dir' => ConvertHelper::boolStrict2string(is_dir($folderPath)),
                        'is_readable' => ConvertHelper::boolStrict2string(is_readable($folderPath)),
                        'file_exists' => ConvertHelper::boolStrict2string(file_exists($folderPath)),
                        'realpath' => realpath($folderPath)
                    ),
                    'iterator' => array(
                        'isDir' => ConvertHelper::boolStrict2string($iterator->isDir()),
                        'getType' => $iterator->getType(),
                        'isLink' => ConvertHelper::boolStrict2string($iterator->isLink()),
                        'isFile' => ConvertHelper::boolStrict2string($iterator->isFile()),
                        'isReadable' => ConvertHelper::boolStrict2string($iterator->isReadable()),
                        'path' => $iterator->getPathname(),
                        'realpath' => $iterator->getRealPath()
                    )
                ), true)
            )
        );
    }
}
