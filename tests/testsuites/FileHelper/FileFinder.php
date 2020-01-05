<?php

use PHPUnit\Framework\TestCase;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

final class FileHelper_FileFinderTest extends TestCase
{
    protected $assetsFolder;
    
    protected function setUp() : void
    {
        $this->assetsFolder = realpath(TESTS_ROOT.'/assets/FileHelper/FileFinder');
        
        if($this->assetsFolder === false) {
            throw new Exception(
                'The file helper assets folder could not be found.'
            );
        }
        
        $this->assetsFolder = FileHelper::normalizePath($this->assetsFolder);
    }
    
    public function test_findFiles_default()
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);
        
        $files = $finder->getAll();
        
        $expected = array(
            $this->assetsFolder.'/.extension',
            $this->assetsFolder.'/README.txt',
            $this->assetsFolder.'/test-png.png'
        );
        
        // ensure the same order for the comparison
        sort($files); sort($expected);
        
        $this->assertEquals($expected, $files);
    }
    
    public function test_findFiles_recursive()
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);
        
        $finder->makeRecursive();
        
        $files = $finder->getAll();
        
        $expected = array(
            $this->assetsFolder.'/.extension',
            $this->assetsFolder.'/README.txt',
            $this->assetsFolder.'/test-png.png',
            $this->assetsFolder.'/Subfolder/script.php'
        );
        
        // ensure the same order for the comparison
        sort($files); sort($expected);
        
        $this->assertEquals($expected, $files);
    }
    
    public function test_findFiles_relative()
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);
        
        $finder->makeRecursive();
        $finder->setPathmodeRelative();
        
        $files = $finder->getAll();
        
        $expected = array(
            '.extension',
            'README.txt',
            'test-png.png',
            'Subfolder/script.php'
        );
        
        // ensure the same order for the comparison
        sort($files); sort($expected);
        
        $this->assertEquals($expected, $files);
    }
    
    public function test_findFiles_stripExtensions()
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);
        
        $finder->makeRecursive();
        $finder->setPathmodeRelative();
        $finder->stripExtensions();
        
        $files = $finder->getAll();
        
        $expected = array(
            'README',
            'test-png',
            'Subfolder/script'
        );
        
        // ensure the same order for the comparison
        sort($files); sort($expected);
        
        $this->assertEquals($expected, $files);
    }
    
    public function test_findFiles_excludeExtensions()
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);
        
        $finder->setPathmodeRelative();
        $finder->excludeExtensions(array('txt'));
        
        $files = $finder->getAll();
        
        $expected = array(
            '.extension',
            'test-png.png',
        );
        
        // ensure the same order for the comparison
        sort($files); sort($expected);
        
        $this->assertEquals($expected, $files);
    }
    
    public function test_findFiles_includeExtensions()
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);
        
        $finder->setPathmodeRelative();
        $finder->includeExtensions(array('txt'));
        
        $files = $finder->getAll();
        
        $expected = array(
            'README.txt',
        );
        
        $this->assertEquals($expected, $files);
    }
    
    public function test_findFiles_pathSeparator()
    {
        $finder = FileHelper::createFileFinder($this->assetsFolder);
        
        $finder->makeRecursive();
        $finder->stripExtensions();
        $finder->setSlashReplacement('-');
        $finder->setPathmodeRelative();
        $finder->includeExtensions(array('php'));
        
        $files = $finder->getAll();
        
        $expected = array(
            'Subfolder-script',
        );
        
        $this->assertEquals($expected, $files);
    }
}
