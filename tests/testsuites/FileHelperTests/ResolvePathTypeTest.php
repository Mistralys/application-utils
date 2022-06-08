<?php

declare(strict_types=1);

namespace testsuites\FileHelperTests;

use AppUtils\FileHelper\AbstractPathInfo;
use DirectoryIterator;
use TestClasses\FileHelperTestCase;

final class ResolvePathTypeTest extends FileHelperTestCase
{
    public function test_resolveTypes() : void
    {
        $iterator = new DirectoryIterator(__DIR__);
        $this->assertTrue($iterator->isDir());
        
        $stringFolder = AbstractPathInfo::resolveType(__DIR__);
        $stringFile = AbstractPathInfo::resolveType(__FILE__);
        $iteratorFolder = AbstractPathInfo::resolveType($iterator);

        $this->assertTrue($stringFolder->isFolder(), 'Should be a folder: ['.$stringFolder.']');
        $this->assertTrue($iteratorFolder->isFolder(), 'Should be a folder: ['.$iteratorFolder.']');
        $this->assertTrue($stringFile->isFile(), 'Should be a file: ['.$stringFile.']');
        $this->assertSame($stringFolder, AbstractPathInfo::resolveType($stringFolder));
    }
}
