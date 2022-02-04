<?php

declare(strict_types=1);

namespace TestClasses;

use RuntimeException;

class FileHelperTestCase extends BaseTestCase
{
    protected const CASE_FILE_LOWER = 'lowercase-extension.case';
    protected const CASE_FILE_UPPER = 'uppercase-extension.CASE';
    protected const CASE_EXTENSION_LOWER = 'case';
    protected const CASE_EXTENSION_UPPER = 'CASE';
    protected const CASE_BASE_LOWER = 'lowercase-extension';
    protected const CASE_BASE_UPPER = 'uppercase-extension';

    /**
     * @var string|NULL
     */
    protected $assetsFolder;

    /**
     * @var string[]
     */
    protected $deleteFiles = array();

    protected function registerFilesToDelete() : void
    {

    }

    protected function registerFileToDelete(string $fileName) : void
    {
        if(!in_array($fileName, $this->deleteFiles))
        {
            $this->deleteFiles[] = $fileName;
        }
    }

    protected function setUp() : void
    {
        $this->registerFilesToDelete();

        $this->initAssetsFolder();
    }

    protected function tearDown() : void
    {
        $this->deleteFiles();
    }

    private function initAssetsFolder() : void
    {
        if(isset($this->assetsFolder))
        {
            return;
        }

        $targetPath = TESTS_ROOT . '/assets/FileHelper';
        $path = realpath($targetPath);

        if($path !== false)
        {
            $this->assetsFolder = $path;
            return;
        }

        throw new RuntimeException(sprintf(
            'The file helper assets folder could not be found at [%s].',
            $targetPath
        ));
    }

    /**
     * Clean up test files between tests.
     * @return void
     */
    private function deleteFiles() : void
    {
        foreach ($this->deleteFiles as $fileName)
        {
            $path = $this->assetsFolder . '/' . $fileName;

            if (file_exists($path))
            {
                $this->assertTrue(
                    unlink($path),
                    sprintf('Cannot remove test file [%s].', $path)
                );
            }
        }
    }
}
