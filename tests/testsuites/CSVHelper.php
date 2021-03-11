<?php

use AppUtils\FileHelper;
use PHPUnit\Framework\TestCase;

final class CSVHelperTest extends TestCase
{
    /**
     * @var string
     */
    protected $assetsFolder;

    protected function setUp(): void
    {
        if (isset($this->assetsFolder)) {
            return;
        }

        $path = TESTS_ROOT . '/assets/CSVHelper';
        $this->assetsFolder = realpath($path);

        if ($this->assetsFolder === false) {
            throw new Exception(
                sprintf('The convert helper assets folder could not be found at [%s].', $path)
            );
        }
    }

    public function test_commaUnquoted() : void
    {
        $data = FileHelper::parseCSVFile($this->assetsFolder.'/comma-unquoted.csv', ',');

        $this->assertNotEmpty($data);
        $this->assertCount(3, $data[0]);
        $this->assertEquals('Column1', $data[0][0]);
        $this->assertEquals('Value01-1', $data[1][0]);
    }

    public function test_semicolonQuoted() : void
    {
        $data = FileHelper::parseCSVFile($this->assetsFolder.'/semicolon-quoted.csv');

        $this->assertNotEmpty($data);
        $this->assertCount(3, $data[0]);
        $this->assertEquals('Column 1', $data[0][0]);
        $this->assertEquals('Value 01-1', $data[1][0]);
    }
}
