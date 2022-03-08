<?php

namespace FileHelperTests;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use TestClasses\FileHelperTestCase;

class LineReaderTest extends FileHelperTestCase
{
    public function test_readLines_all() : void
    {
        $file = $this->assetsFolder . '/line-seeking.txt';

        $lines = FileHelper::readLines($file);
        $this->assertCount(10, $lines, 'Should have read all 10 lines from the file.');
    }

    public function test_readLines() : void
    {
        $file = $this->assetsFolder . '/line-seeking.txt';

        $lines = FileHelper::readLines($file, 5);
        $lines = array_map('trim', $lines); // to make the comparison easier

        $this->assertEquals($lines, array('1', '2', '3', '4', '5'));
    }

    public function test_readLines_emptyFile() : void
    {
        $file = $this->assetsFolder . '/zero-length.txt';

        $lines = FileHelper::readLines($file, 5);

        $this->assertEquals($lines, array());
    }

    public function test_readLines_bomFile() : void
    {
        $file = $this->assetsFolder . '/bom-utf8.txt';

        $lines = FileHelper::readLines($file, 5);

        $this->assertEquals($lines, array('Test text.'));
    }

    public function test_readLines_fileNotExists() : void
    {
        $file = $this->assetsFolder . '/unknown-file.txt';

        $this->expectException(FileHelper_Exception::class);

        FileHelper::readLines($file, 5);
    }

    /**
     * Try fetching a specific line from a file.
     */
    public function test_getLineFromFile() : void
    {
        $file = $this->assetsFolder . '/line-seeking.txt';

        $line3 = trim(FileHelper::getLineFromFile($file, 3));

        $this->assertEquals('3', $line3, 'Should read line nr 3');
    }

    /**
     * Try reading a line number that does not exist.
     */
    public function test_getLineFromFile_outOfBounds() : void
    {
        $file = $this->assetsFolder . '/line-seeking.txt';

        $line = FileHelper::getLineFromFile($file, 30);

        $this->assertEquals(null, $line, 'Should be NULL when line number does not exist.');
    }

    /**
     * Try reading from a file that does not exist.
     */
    public function test_getLineFromFile_fileNotExists() : void
    {
        $file = '/path/to/unknown/file.txt';

        $this->expectException(FileHelper_Exception::class);

        FileHelper::getLineFromFile($file, 3);
    }

    /**
     * Test a simple line count.
     */
    public function test_countFileLines() : void
    {
        $file = $this->assetsFolder . '/line-seeking.txt';

        $result = FileHelper::countFileLines($file);

        $this->assertEquals(10, $result, 'Should be 10 lines in the file.');
    }

    /**
     * Test counting the lines in a zero length file,
     * meaning without any contents at all.
     */
    public function test_countFileLines_zeroLength() : void
    {
        $file = $this->assetsFolder . '/zero-length.txt';

        $result = FileHelper::countFileLines($file);

        $this->assertEquals(0, $result, 'Should not be any lines at all in the file.');
    }

    /**
     * Test counting lines in a file with a single line, with
     * no newline at the end.
     */
    public function test_countFileLines_singleLine() : void
    {
        $file = $this->assetsFolder . '/single-line.txt';

        $result = FileHelper::countFileLines($file);

        $this->assertEquals(1, $result, 'Should be a single line in the file.');
    }

    /**
     * Test counting lines in a file with a single space as content.
     */
    public function test_countFileLines_whitespace() : void
    {
        $file = $this->assetsFolder . '/whitespace.txt';

        $result = FileHelper::countFileLines($file);

        $this->assertEquals(1, $result, 'Should be a single line in the file.');
    }
}
