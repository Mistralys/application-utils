<?php

declare(strict_types=1);

namespace FileHelperTests;

use AppUtils\FileHelper;
use AppUtils\FileHelper\SerializedFile;
use AppUtils\FileHelper_Exception;
use TestClasses\FileHelperTestCase;

class SerializedFileTest extends FileHelperTestCase
{
    // region: _Tests

    public function test_createFile() : void
    {
        $file = SerializedFile::factory($this->testFileWrite);
        $file->putData(array('foo' => 'bar'));

        $this->assertFileExists($this->testFileWrite);
        $this->assertSame(array('foo' => 'bar'), unserialize(file_get_contents($this->testFileWrite)));
    }

    /**
     * @see FileHelper::parseSerializedFile()
     */
    public function test_parseSerializedFile() : void
    {
        $refData = array('key' => 'value', 'utf8' => 'öäüé');
        $expected = json_encode($refData, JSON_THROW_ON_ERROR);

        $result = FileHelper::parseSerializedFile($this->testFile);

        $this->assertEquals($expected, json_encode($result, JSON_THROW_ON_ERROR));
    }

    /**
     * @see FileHelper::parseSerializedFile()
     */
    public function test_parseSerializedFile_fileNotExists() : void
    {
        $this->expectException(FileHelper_Exception::class);

        FileHelper::parseSerializedFile('/path/to/unknown.ser');
    }

    /**
     * @see FileHelper::parseSerializedFile()
     */
    public function test_parseSerializedFile_fileNotUnserializable() : void
    {
        $this->expectException(FileHelper_Exception::class);

        FileHelper::parseSerializedFile($this->testFileBroken);
    }

    // endregion

    // region: Support methods

    private string $testFileBroken;
    private string $testFile;
    private string $testFileWrite;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testFile = $this->assetsFolder . '/serialized.ser';
        $this->testFileBroken = $this->assetsFolder . '/serialized-broken.ser';
        $this->testFileWrite = $this->assetsFolder . '/serialized-write.ser';

        if(file_exists($this->testFileWrite)) {
            unlink($this->testFileWrite);
        }
    }

    // endregion
}
