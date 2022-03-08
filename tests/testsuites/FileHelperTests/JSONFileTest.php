<?php

declare(strict_types=1);

namespace FileHelperTests;

use AppUtils\FileHelper;
use TestClasses\FileHelperTestCase;

class JSONFileTest extends FileHelperTestCase
{
    protected const TEST_FILE_VALID = 'json.json';
    protected const TEST_FILE_INVALID = 'json-broken.json';
    protected const TEST_FILE_VALID_KEY = 'test';
    protected const TEST_FILE_VALID_VALUE = 'okay';
    protected const TEST_FILE_WRITE = 'json-write.json';

    protected function registerFilesToDelete() : void
    {
        $this->registerFileToDelete(self::TEST_FILE_WRITE);
    }

    public function test_parseValid() : void
    {
        $targetFile = $this->assetsFolder.'/'.self::TEST_FILE_VALID;
        $data = FileHelper::parseJSONFile($targetFile);

        $this->assertArrayHasKey( self::TEST_FILE_VALID_KEY, $data);
        $this->assertSame(self::TEST_FILE_VALID_VALUE, $data[self::TEST_FILE_VALID_KEY]);
    }

    public function test_parseInvalid() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_CANNOT_DECODE_JSON_FILE);

        FileHelper::parseJSONFile($this->assetsFolder.'/'.self::TEST_FILE_INVALID);
    }

    public function test_fileNotExists() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_FILE_DOES_NOT_EXIST);

        FileHelper::parseJSONFile('unknown/path/to/file.json');
    }

    public function test_putData() : void
    {
        $targetFile = $this->assetsFolder . '/' . self::TEST_FILE_WRITE;
        $data = array(
            self::TEST_FILE_VALID_KEY => self::TEST_FILE_VALID_VALUE
        );

        FileHelper::saveAsJSON($data, $targetFile);

        $this->assertSame($data, FileHelper::parseJSONFile($targetFile));
    }
}
