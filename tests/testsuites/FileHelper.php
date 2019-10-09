<?php

use PHPUnit\Framework\TestCase;

use AppUtils\FileHelper;

final class FileHelperTest extends TestCase
{
    protected $assetsFolder;
    
    protected function setUp() : void
    {
        if(isset($this->assetsFolder)) {
            return;
        }
        
        $this->assetsFolder = realpath(__DIR__.'/../assets/FileHelper');
        
        if($this->assetsFolder === false) {
            throw new Exception(
                'The file helper assets folder could not be found.'
            );
        }
    }
    
    public function test_relativizePathByDepth()
    {
        $tests = array(
            array(
                'path' => 'c:\\',
                'result' => '',
                'depth' => 2
            ),
            array(
                'path' => 'f:\file.txt',
                'result' => 'file.txt',
                'depth' => 2
            ),
            array(
                'path' => 'c:\path\to\file.txt',
                'result' => 'path/to/file.txt',
                'depth' => 2
            ),
            array(
                'path' => 'c:\path\to\some\other\file.txt',
                'result' => 'some/other/file.txt',
                'depth' => 2
            ),
            array(
                'path' => 'g:\path\to\folder',
                'result' => 'path/to/folder',
                'depth' => 2
            ),
            array(
                'path' => 'g:\path\to\folder\\',
                'result' => 'path/to/folder',
                'depth' => 2
            ),
            array(
                'path' => '/path/to/folder/and/even/further/down/the/road/',
                'result' => 'down/the/road',
                'depth' => 2
            ),
            array(
                'path' => '/path/to/folder/and/even/further/down/the/road/',
                'result' => 'and/even/further/down/the/road',
                'depth' => 5
            ),
        );
        
        foreach($tests as $def)
        {
            $this->assertEquals($def['result'], FileHelper::relativizePathByDepth($def['path'], $def['depth']));
        }
    }
    
    public function test_relativizePath()
    {
        $tests = array(
            array(
                'path' => 'c:\test\folder\here',
                'relativeTo' => 'c:\test',
                'result' => 'folder/here',
            ),
            array(
                'path' => 'f:\file.txt',
                'relativeTo' => 'f:',
                'result' => 'file.txt',
            ),
            array(
                'path' => 'g:\file.txt',
                'relativeTo' => 'f:\file.txt',
                'result' => 'g:/file.txt',
            ),
        );
        
        foreach($tests as $def)
        {
            $this->assertEquals($def['result'], FileHelper::relativizePath($def['path'], $def['relativeTo']));
        }
    }
    
    /**
     * @see FileHelper::removeExtension()
     */
    public function test_removeExtension()
    {
        $tests = array(
            'somename.ext' => 'somename',
            '/path/to/file.txt' => 'file',
            'F:\\path\name.extension' => 'name',
            'With.Several.Dots.file' => 'With.Several.Dots',
            'noextension' => 'noextension',
            'file ending in dot.' => 'file ending in dot',
            '.ext' => ''
        );
        
        foreach($tests as $string => $expected)
        {
            $actual = FileHelper::removeExtension($string);
            
            $this->assertEquals($expected, $actual);
        }
    }
    
   /**
    * @see FileHelper::detectUTFBom()
    */
    public function test_detectUTF8BOM()
    {
        $files = array(
            '16-big-endian' => 'UTF16-BE',
            '16-little-endian' => 'UTF16-LE',
            '32-big-endian' => 'UTF32-BE',
            '32-little-endian' => 'UTF32-LE',
            '8' => 'UTF8'
        );
        
        foreach($files as $name => $expected)
        {
            $result = FileHelper::detectUTFBom($this->assetsFolder.'/bom-utf'.$name.'.txt');
            
            $this->assertEquals($expected, $result, 'Did not detect the correct unicode file encoding.');
        }
    }
    
   /**
    * @see FileHelper::fixFileName()
    */
    public function test_fixFileName()
    {
        $tests = array(
            ' test.ext' => 'test.ext',
            'name..ext' => 'name.ext',
            'test     .txt' => 'test.txt',
            '/path/to/file.ext' => 'file.ext',
            '\path\to\file.ext' => 'file.ext',
            'file. ext' => 'file.ext',
            'file .ext' => 'file.ext',
            'file.ext     ' => 'file.ext',
            "file\t.ext" => 'file.ext',
            'file here.ext' => 'file here.ext'
        );
        
        foreach($tests as $source => $expected)
        {
            $result = FileHelper::fixFileName($source);
            
            $this->assertEquals($expected, $result, 'The corrected file name does not match.');
        }
    }
    
    public function test_getExtension()
    {
        $tests = array(
            array(
                'label' => 'Simple extension',
                'path' => '/path/to/file.txt',
                'expected' => 'txt',
            ),
            array(
                'label' => 'Lowercase extension by default',
                'path' => '/path/to/file.TXT',
                'expected' => 'txt',
            ),
            array(
                'label' => 'Lowercase extension, explicit',
                'path' => '/path/to/file.TXT',
                'expected' => 'txt',
                'lowercase' => true
            ),
            array(
                'label' => 'Keep original extension case',
                'path' => '/path/to/file.TXT',
                'expected' => 'TXT',
                'lowercase' => false
            ),
            array(
                'label' => 'No extension (path with slash)',
                'path' => '/path/to/',
                'expected' => '',
            ),
            array(
                'label' => 'No extension (path without slash)',
                'path' => '/path/to',
                'expected' => '',
            ),
            array(
                'label' => 'Regular file name',
                'path' => 'somefile.ext',
                'expected' => 'ext',
            ),
            array(
                'label' => 'File name with several dots',
                'path' => 'file.with.several.dots',
                'expected' => 'dots',
            ),
            array(
                'label' => 'Windows style path',
                'path' => '\path\to\file.txt',
                'expected' => 'txt',
            ),
            array(
                'label' => 'Dot only notation',
                'path' => '.txt',
                'expected' => 'txt',
            )
        );
        
        foreach($tests as $def) 
        {
            if(!isset($def['lowercase'])) {
                $result = FileHelper::getExtension($def['path']);
            } else {
                $result = FileHelper::getExtension($def['path'], $def['lowercase']);
            }
            
            $this->assertEquals($def['expected'], $result, $def['label']);
        }
    }
   
    public function test_getExtension_directoryIterator()
    {
        $files = array(
            'lowercase-extension.case' => array
            (
                array(
                    'label' => 'Regular lowercase extension',
                    'expected' => 'case',
                )
            ),
            'uppercase-extension.CASE' => array
            (
                array(
                    'label' => 'Uppercase extension, default lowercased',
                    'expected' => 'case',                
                ),
                array(
                    'label' => 'Uppercase extension, no case change',
                    'expected' => 'CASE',
                    'lowercase' => false
                )
            )
        );
        
        $d = new DirectoryIterator($this->assetsFolder);
        
        foreach($d as $item) 
        {
            if(!isset($files[$item->getFilename()])) {
                continue;
            }
            
            $tests = $files[$item->getFilename()];
            
            foreach($tests as $def) 
            {
                if(!isset($def['lowercase'])) {
                    $result = FileHelper::getExtension($item);
                } else {
                    $result = FileHelper::getExtension($item, $def['lowercase']);
                }
                
                $this->assertEquals($def['expected'], $result, $def['label']);
            }
        }
    }
}
