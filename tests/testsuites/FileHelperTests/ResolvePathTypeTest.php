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

    /**
     * Strange behavior: using the __DIR__ constant yields
     * a regular path, but using this in a directory iterator
     * causes the path to show up as one of the test PHP files
     * instead (FolderInfoTest.php) when running in Travis or
     * Scrutinizer on GitHub.
     *
     * Doubly strange, because only the DirectoryIterator was
     * affected. The regular PHP methods like is_dir returned
     * the correct results.
     *
     * There was no point in doing any more research, so the
     * tests now use paths to files and folders from the test
     * assets, which works as expected.
     *
     * One can only assume that this is a specificity of the
     * file system in Travis and Scrutinizer, as tests on both
     * Windows and MacOS went through without any issues.
     */
    public function test_controlIteratorBehavior_Constant() : void
    {
        $folderPath = __DIR__;

        $this->debugIterator($folderPath);
    }

    public function test_resolveType_FolderString() : void
    {
        $stringFolder = AbstractPathInfo::resolveType($this->folder);

        $this->assertTrue(
            $stringFolder->isFolder(),
            'Must be detected as a folder: ['.$stringFolder.']'
        );
    }

    public function test_resolveType_FolderIterator() : void
    {
        $iterator = new DirectoryIterator($this->folder);
        $this->assertSame($this->folder, $iterator->getPath());
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
        $stringFolder = AbstractPathInfo::resolveType($this->folder);

        $this->assertSame($stringFolder, AbstractPathInfo::resolveType($stringFolder));
    }

    public function test_resolveType_File() : void
    {
        $stringFile = AbstractPathInfo::resolveType($this->file);

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
                        'realpath' => $iterator->getRealPath(),
                        'files' => $this->getFiles($iterator)
                    )
                ), true)
            )
        );
    }

    private string $folder;
    private string $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->folder = __DIR__.'/../../assets/FileHelper/FolderTree';
        $this->file = __DIR__.'/../../assets/FileHelper/single-line.txt';

        // Check prerequisites
        $this->assertTrue(is_readable($this->folder));
        $this->assertTrue(is_dir($this->folder));
        $this->assertTrue(is_readable($this->file));
        $this->assertTrue(is_file($this->file));
    }

    /**
     * @param DirectoryIterator $iterator
     * @return string[]
     */
    private function getFiles(DirectoryIterator $iterator) : array
    {
        $result = array();

        foreach($iterator as $entry)
        {
            $result[] = $entry->getPathname();
        }

        return $result;
    }
}
