<?php

use PHPUnit\Framework\TestCase;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\ConvertHelper_EOL;

final class FileHelperTest extends TestCase
{
    protected $assetsFolder;
    
    protected $deleteFiles = array(
        'savetest.txt'
    );
    
    protected function setUp() : void
    {
        if(isset($this->assetsFolder)) 
        {
            // remove any test files from the last test
            foreach($this->deleteFiles as $fileName) 
            {
                $path = $this->assetsFolder.'/'.$fileName;
                if(file_exists($path)) {
                    $this->assertTrue(unlink($this->assetsFolder.'/savetest.txt'), 'Cannot remove test file.');
                }
            }
            
            return;
        }
        
        $this->assetsFolder = realpath(TESTS_ROOT.'/assets/FileHelper');
        
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
    
   /**
    * @see FileHelper::relativizePath()
    */
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
            array(
                'path' => '/path/to/some/file.txt',
                'relativeTo' => '/path',
                'result' => 'to/some/file.txt',
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
    * @see FileHelper::isValidUnicodeEncoding()
    */
    public function test_isValidUnicodeEncoding()
    {
        $tests = array(
            'UTF16-LE' => true,
            'UTF32-BE' => true,
            'UTF-32-LE' => true,
            'UTF32' => true,
            'UTF-16' => true,
            'UTF8' => true,
            'UTF-8' => true,
            'somestring' => false,
            '' => false
        );
        
        foreach($tests as $encoding => $expected)
        {
            $result = FileHelper::isValidUnicodeEncoding($encoding);
            
            $this->assertEquals($expected, $result, 'Encoding ['.$encoding.'] does not match expected result.');
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
    
   /**
    * @see FileHelper::getExtension()
    */
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
            ),
            array(
                'label' => 'Lowercase special characters',
                'path' => '.ÖÉÜ',
                'expected' => 'öéü',
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
   
   /**
    * @see FileHelper::getExtension()
    */
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
    
   /**
    * @see FileHelper::detectMimeType()
    */
    function test_detectMimeType()
    {
        $tests = array(
            'mime.json' => 'application/json',
            'mime.jpg' => 'image/jpeg',
            'mime.jpeg' => 'image/jpeg',
            'mime.csv' => 'text/csv',
            'mime.xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'mime.mp4' => 'video/mp4',
            'mime.pdf' => 'application/pdf',
            'noextension' => null,
            'mime.unknown' => null
        );
        
        foreach($tests as $filename => $expected)
        {
            $result = FileHelper::detectMimeType($filename);
            
            $this->assertEquals($expected, $result, 'Mime type does not match file extension.');
        }
    }
    
   /**
    * @see FileHelper::getFilename()
    */
    function test_getFileName()
    {
        $tests = array(
            array(
                'label' => 'File name with path, default with extension',
                'path' => '/path/to/file.ext',
                'expected' => 'file.ext'
            ),
            array(
                'label' => 'File name with path, explicitly with extension',
                'path' => '/path/to/file.ext',
                'expected' => 'file.ext',
                'extension' => true
            ),
            array(
                'label' => 'File name with path, explicitly without extension',
                'path' => '/path/to/file.ext',
                'expected' => 'file',
                'extension' => false
            ),
            array(
                'label' => 'File name with Windows style path',
                'path' => 'c:\path\to\file.ext',
                'expected' => 'file.ext'
            ),
            array(
                'label' => 'File name with Windows style path without extension',
                'path' => 'c:\path\to\file.ext',
                'expected' => 'file',
                'extension' => false
            ),
            array(
                'label' => 'Windows style path without file name, with trailing slash',
                'path' => 'c:\path\to\\',
                'expected' => 'to'
            ),
            array(
                'label' => 'Windows style path without file name, without trailing slash',
                'path' => 'c:\path\to',
                'expected' => 'to'
            ),
            array(
                'label' => 'Regular path without file name, with trailing slash',
                'path' => '/path/to/',
                'expected' => 'to'
            ),
            array(
                'label' => 'Regular path without file name, without trailing slash',
                'path' => '/path/to',
                'expected' => 'to'
            ),
            array(
                'label' => 'Simple filename without path',
                'path' => 'file.ext',
                'expected' => 'file.ext'
            ),
            array(
                'label' => 'Simple filename without path, with several dots',
                'path' => 'file.with.several.dots.ext',
                'expected' => 'file.with.several.dots.ext'
            ),
            array(
                'label' => 'Simple filename without path, with several dots, extension OFF',
                'path' => 'file.with.several.dots.ext',
                'expected' => 'file.with.several.dots',
                'extension' => false
            ),
            array(
                'label' => 'Simple filename without path, with mixed case',
                'path' => 'File.EXT',
                'expected' => 'File.EXT'
            )
        );
        
        foreach($tests as $def)
        {
            if(!isset($def['extension'])) {
                $result = FileHelper::getFilename($def['path']);
            } else {
                $result = FileHelper::getFilename($def['path'], $def['extension']);
            }
            
            $this->assertEquals($def['expected'], $result, $def['label']);
        }
    }
    
   /**
    * @see FileHelper::getMaxUploadFilesize() 
    */
    function test_getUploadMaxFilesize()
    {
        // configured for the tests in the tests batch file, or
        // in the travis yaml setup.
        $mb = 6; 
        $string = $mb.'M';
        
        if(ini_get('upload_max_filesize') !== $string || ini_get('post_max_size') !== $string) {
            $this->markTestSkipped('The ini settings do not match the expected value.');
            return;
        }
        
        $expected = $mb * 1048576; // binary notation (1KB = 1024B)
        
        $result = FileHelper::getMaxUploadFilesize();
        
        $this->assertEquals($expected, $result);
    }
    
   /**
    * @see FileHelper::normalizePath()
    */
    public function test_normalizePath()
    {
        $tests = array(
            '/path/to/somewhere' => '/path/to/somewhere',
            'c:\windows\style\path' => 'c:/windows/style/path',
            'path/with/slash/' => 'path/with/slash/',
            'd:\mixed/style\here' => 'd:/mixed/style/here',
            '/with/file.txt' => '/with/file.txt'
         );
        
        foreach($tests as $path => $expected) 
        {
            $result = FileHelper::normalizePath($path);
            
            $this->assertEquals($expected, $result);
        }
    }
    
   /**
    * @see FileHelper::parseSerializedFile()
    */
    public function test_parseSerializedFile()
    {
        $file = $this->assetsFolder.'/serialized.ser';
        
        $refData = array('key' => 'value', 'utf8' => 'öäüé');
        $expected = json_encode($refData);
        
        $result = FileHelper::parseSerializedFile($file);
        
        $this->assertEquals($expected, json_encode($result));
    }
    
   /**
    * @see FileHelper::parseSerializedFile()
    */
    public function test_parseSerializedFile_fileNotExists()
    {
        $file = $this->assetsFolder.'/unknown.ser';

        $this->expectException(FileHelper_Exception::class);
        
        $result = FileHelper::parseSerializedFile($file);
    }
    
   /**
    * @see FileHelper::parseSerializedFile()
    */
    public function test_parseSerializedFile_fileNotUnserializable()
    {
        $file = $this->assetsFolder.'/serialized-broken.ser';
        
        $this->expectException(FileHelper_Exception::class);
        
        $result = FileHelper::parseSerializedFile($file);
    }

   /**
    * @see FileHelper::cliCommandExists()
    */
    public function test_cliCommandExists()
    {
        $output = array();
        exec('php -v 2>&1', $output);
        
        $available = $result = !empty($output);
        
        $this->assertEquals($available, FileHelper::cliCommandExists('php'));
    }
    
   /**
    * Try fetching a specific line from a file.
    */
    public function test_getLineFromFile()
    {
        $file = $this->assetsFolder.'/line-seeking.txt';
        
        $line3 = trim(FileHelper::getLineFromFile($file, 3));
        
        $this->assertEquals('3', $line3, 'Should read line nr 3');
    }
    
   /**
    * Try reading a line number that does not exist.
    */
    public function test_getLineFromFile_outOfBounds()
    {
        $file = $this->assetsFolder.'/line-seeking.txt';
        
        $line = FileHelper::getLineFromFile($file, 30);
        
        $this->assertEquals(null, $line, 'Should be NULL when line number does not exist.');
    }
    
   /**
    * Try reading from a file that does not exist.
    */
    public function test_getLineFromFile_fileNotExists()
    {
        $file = '/path/to/unknown/file.txt';
        
        $this->expectException(FileHelper_Exception::class);
        
        FileHelper::getLineFromFile($file, 3);
    }
    
   /**
    * Test a simple line count.
    */
    public function test_countFileLines()
    {
        $file = $this->assetsFolder.'/line-seeking.txt';
        
        $result = FileHelper::countFileLines($file);
        
        $this->assertEquals(10, $result, 'Should be 10 lines in the file.');
    }
    
   /**
    * Test counting the lines in a zero length file,
    * meaning without any contents at all.
    */
    public function test_countFileLines_zeroLength()
    {
        $file = $this->assetsFolder.'/zero-length.txt';
        
        $result = FileHelper::countFileLines($file);
        
        $this->assertEquals(0, $result, 'Should not be any lines at all in the file.');
    }
   
   /**
    * Test counting lines in a file with a single line, with
    * no newline at the end.
    */ 
    public function test_countFileLines_singleLine()
    {
        $file = $this->assetsFolder.'/single-line.txt';
        
        $result = FileHelper::countFileLines($file);
        
        $this->assertEquals(1, $result, 'Should be a single line in the file.');
    }

   /**
    * Test counting lines in a file with a single space as content.
    */
    public function test_countFileLines_whitespace()
    {
        $file = $this->assetsFolder.'/whitespace.txt';
        
        $result = FileHelper::countFileLines($file);
        
        $this->assertEquals(1, $result, 'Should be a single line in the file.');
    }
    
    public function test_saveFile()
    {
         $file = $this->assetsFolder.'/savetest.txt';
         
         FileHelper::saveFile($file, 'Hoho');
         
         $this->assertEquals('Hoho', file_get_contents($file));
    }

    public function test_saveFile_empty()
    {
        $file = $this->assetsFolder.'/savetest.txt';
        
        FileHelper::saveFile($file);
        
        $this->assertEquals('', file_get_contents($file));
    }
    
    public function test_readLines()
    {
        $file = $this->assetsFolder.'/line-seeking.txt';
        
        $lines = FileHelper::readLines($file, 5);
        $lines = array_map('trim', $lines); // to make the comparison easier
        
        $this->assertEquals($lines, array('1', '2', '3', '4', '5'));
    }
    
    public function test_readLines_emptyFile()
    {
        $file = $this->assetsFolder.'/zero-length.txt';
        
        $lines = FileHelper::readLines($file, 5);
        
        $this->assertEquals($lines, array());
    }
    
    public function test_readLines_bomFile()
    {
        $file = $this->assetsFolder.'/bom-utf8.txt';
        
        $lines = FileHelper::readLines($file, 5);
        
        $this->assertEquals($lines, array('Test text.'));
    }
    
    public function test_readLines_fileNotExists()
    {
        $file = $this->assetsFolder.'/unknown-file.txt';
        
        $this->expectException(FileHelper_Exception::class);
        
        $lines = FileHelper::readLines($file, 5);
    }
    
    public function test_detectEOL()
    {
        $tests = array(
            array(
                'label' => 'CRLF',
                'file' => 'eol-crlf.txt',
                'type' => ConvertHelper_EOL::TYPE_CRLF,
                'isCRLF' => true,
                'isLF' => false,
                'isCR' => false
            ),
            array(
                'label' => 'LF',
                'file' => 'eol-lf.txt',
                'type' => ConvertHelper_EOL::TYPE_LF,
                'isCRLF' => false,
                'isLF' => true,
                'isCR' => false
            ),
            array(
                'label' => 'CR',
                'file' => 'eol-cr.txt',
                'type' => ConvertHelper_EOL::TYPE_CR,
                'isCRLF' => false,
                'isLF' => false,
                'isCR' => true
            )
        );
        
        foreach($tests as $test)
        {
            $file = $this->assetsFolder.'/'.$test['file'];
        
            $result = FileHelper::detectEOLCharacter($file);
            
            $label = $test['label'].' in file '.$test['file'];
            
            $this->assertInstanceof(\AppUtils\ConvertHelper_EOL::class, $result, $label);
            $this->assertEquals($test['type'], $result->getType(), $label);
            $this->assertEquals($test['isCRLF'], $result->isCRLF(), $label);
            $this->assertEquals($test['isCR'], $result->isCR(), $label);
            $this->assertEquals($test['isLF'], $result->isLF(), $label);
        }
    }
}
