<?php

use PHPUnit\Framework\TestCase;

use AppUtils\FileHelper;

final class FileHelperTest extends TestCase
{
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
    
}
