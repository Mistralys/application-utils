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
        $stringFolder = AbstractPathInfo::resolveType(__DIR__);
        $stringFile = AbstractPathInfo::resolveType(__FILE__);
        $iteratorFolder = AbstractPathInfo::resolveType(new DirectoryIterator(__DIR__));

        $this->assertTrue($stringFolder->isFolder());
        $this->assertTrue($iteratorFolder->isFolder());
        $this->assertTrue($stringFile->isFile());
        $this->assertSame($stringFolder, AbstractPathInfo::resolveType($stringFolder));
    }
}
