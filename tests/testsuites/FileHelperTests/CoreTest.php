<?php

declare(strict_types=1);

namespace FileHelperTests;

use AppUtils\ConvertHelper_EOL;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\FileHelper_MimeTypes;
use DirectoryIterator;
use TestClasses\FileHelperTestCase;
use function AppUtils\sb;

final class CoreTest extends FileHelperTestCase
{
    protected const SAVE_TEST_FILE = 'savetest.txt';

    protected function registerFilesToDelete() : void
    {
        $this->registerFileToDelete(self::SAVE_TEST_FILE);
    }

    public function test_relativizePathByDepth() : void
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

        foreach ($tests as $def)
        {
            $this->assertEquals($def['result'], FileHelper::relativizePathByDepth($def['path'], $def['depth']));
        }
    }

    /**
     * @see FileHelper::relativizePath()
     */
    public function test_relativizePath() : void
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

        foreach ($tests as $def)
        {
            $this->assertEquals($def['result'], FileHelper::relativizePath($def['path'], $def['relativeTo']));
        }
    }

    /**
     * @see FileHelper::removeExtension()
     */
    public function test_removeExtension() : void
    {
        $tests = array(
            'somename.ext' => 'somename',
            '/path/to/file.txt' => 'file',
            'F:\\path\name.extension' => 'name',
            'With.Several.Dots.file' => 'With.Several.Dots',
            '.ext' => ''
        );

        foreach ($tests as $string => $expected)
        {
            $actual = FileHelper::removeExtension($string);

            $this->assertEquals($expected, $actual);
        }
    }

    public function test_removeExtension_keepPath() : void
    {
        $tests = array(
            'somename.ext' => 'somename',
            '/path/to/file.txt' => '/path/to/file',
            'F:\\path\name.extension' => 'F:/path/name',
            'With.Several.Dots.file' => 'With.Several.Dots',
            '.ext' => ''
        );

        foreach ($tests as $string => $expected)
        {
            $actual = FileHelper::removeExtension($string, true);

            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @see FileHelper::detectUTFBom()
     */
    public function test_detectUTF8BOM() : void
    {
        $files = array(
            '16-big-endian' => 'UTF16-BE',
            '16-little-endian' => 'UTF16-LE',
            '32-big-endian' => 'UTF32-BE',
            '32-little-endian' => 'UTF32-LE',
            '8' => 'UTF8'
        );

        foreach ($files as $name => $expected)
        {
            $result = FileHelper::createUnicodeHandling()
                ->detectUTFBom($this->assetsFolder . '/bom-utf' . $name . '.txt');

            $this->assertEquals($expected, $result, 'Did not detect the correct unicode file encoding.');
        }
    }

    /**
     * @see FileHelper::isValidUnicodeEncoding()
     */
    public function test_isValidUnicodeEncoding() : void
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

        foreach ($tests as $encoding => $expected)
        {
            $result = FileHelper::createUnicodeHandling()->isValidEncoding($encoding);

            $this->assertEquals($expected, $result, 'Encoding [' . $encoding . '] does not match expected result.');
        }
    }

    /**
     * @see FileHelper::fixFileName()
     */
    public function test_fixFileName() : void
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

        foreach ($tests as $source => $expected)
        {
            $result = FileHelper::fixFileName($source);

            $this->assertEquals($expected, $result, 'The corrected file name does not match.');
        }
    }

    /**
     * @see FileHelper::getExtension()
     */
    public function test_getExtension() : void
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

        foreach ($tests as $def)
        {
            if (!isset($def['lowercase']))
            {
                $result = FileHelper::getExtension($def['path']);
            }
            else
            {
                $result = FileHelper::getExtension($def['path'], $def['lowercase']);
            }

            $this->assertEquals($def['expected'], $result, $def['label']);
        }
    }

    public function test_getPathInfoEmpty() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_INVALID);

        FileHelper::getPathInfo('');
    }

    /**
     * @see FileHelper::getExtension()
     */
    public function test_getExtension_directoryIterator() : void
    {
        $files = array(
            self::CASE_FILE_LOWER => array
            (
                array(
                    'label' => 'Regular lowercase extension',
                    'expected' => self::CASE_EXTENSION_LOWER,
                )
            ),
            self::CASE_FILE_UPPER => array
            (
                array(
                    'label' => 'Uppercase extension, default lowercased',
                    'expected' => self::CASE_EXTENSION_LOWER,
                ),
                array(
                    'label' => 'Uppercase extension, no case change',
                    'expected' => self::CASE_EXTENSION_UPPER,
                    'lowercase' => false
                )
            )
        );

        $d = new DirectoryIterator($this->assetsFolder);

        foreach ($d as $item)
        {
            if (!isset($files[$item->getFilename()]))
            {
                continue;
            }

            $tests = $files[$item->getFilename()];

            foreach ($tests as $def)
            {
                $lowercase = $def['lowercase'] ?? true;
                $result = FileHelper::getExtension($item, $lowercase);

                $this->assertEquals(
                    $def['expected'],
                    $result,
                    (string)sb()
                        ->setSeparator('')
                        ->add($def['label'])
                        ->eol()
                        ->sf('File: %s', $item->getFilename())
                        ->eol()
                        ->sf('Lowercase: %s', sb()->bool($lowercase))
                );
            }
        }
    }

    /**
     * @see FileHelper::detectMimeType()
     */
    public function test_detectMimeType() : void
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

        foreach ($tests as $filename => $expected)
        {
            $result = FileHelper::detectMimeType($filename);

            $this->assertEquals($expected, $result, 'Mime type does not match file extension.');
        }
    }

    /**
     * @see FileHelper::detectMimeType()
     */
    public function test_detectCustomMimeType() : void
    {
        $tests = array(
            'mime.push' => 'application/json',
            'mime.sms' => 'text/plain',
            'mime.jpeg' => 'text/plain'
        );

        FileHelper_MimeTypes::registerCustom('push', 'application/json');
        FileHelper_MimeTypes::registerCustom('sms', 'text/plain');
        FileHelper_MimeTypes::setMimeType('jpeg', 'text/plain');

        foreach ($tests as $filename => $expected)
        {
            $result = FileHelper::detectMimeType($filename);

            $this->assertEquals($expected, $result, 'Mime type does not match file extension.');
        }
    }

    /**
     * @see FileHelper::getFilename()
     */
    public function test_getFileName() : void
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

        foreach ($tests as $def)
        {
            if (!isset($def['extension']))
            {
                $result = FileHelper::getFilename($def['path']);
            }
            else
            {
                $result = FileHelper::getFilename($def['path'], $def['extension']);
            }

            $this->assertEquals($def['expected'], $result, $def['label']);
        }
    }

    /**
     * @see FileHelper::getMaxUploadFilesize()
     */
    public function test_getUploadMaxFilesize() : void
    {
        // configured for the tests in the tests batch file, or
        // in the travis yaml setup.
        $mb = 6;
        $string = $mb . 'M';

        if (ini_get('upload_max_filesize') !== $string || ini_get('post_max_size') !== $string)
        {
            $this->markTestSkipped('The ini settings do not match the expected value.');
        }

        $expected = $mb * 1048576; // binary notation (1KB = 1024B)

        $result = FileHelper::getMaxUploadFilesize();

        $this->assertEquals($expected, $result);
    }

    /**
     * @see FileHelper::normalizePath()
     */
    public function test_normalizePath() : void
    {
        $tests = array(
            '/path/to/somewhere' => '/path/to/somewhere',
            'c:\windows\style\path' => 'c:/windows/style/path',
            'path/with/slash/' => 'path/with/slash/',
            'd:\mixed/style\here' => 'd:/mixed/style/here',
            '/with/file.txt' => '/with/file.txt',
            '/with//double//slashes' => '/with/double/slashes',
            '\\mixed\\style\/path\/windows\/style' => '/mixed/style/path/windows/style'
        );

        foreach ($tests as $path => $expected)
        {
            $result = FileHelper::normalizePath($path);

            $this->assertEquals($expected, $result);
        }
    }

    /**
     * @see FileHelper::parseSerializedFile()
     */
    public function test_parseSerializedFile() : void
    {
        $file = $this->assetsFolder . '/serialized.ser';

        $refData = array('key' => 'value', 'utf8' => 'öäüé');
        $expected = json_encode($refData, JSON_THROW_ON_ERROR);

        $result = FileHelper::parseSerializedFile($file);

        $this->assertEquals($expected, json_encode($result, JSON_THROW_ON_ERROR));
    }

    /**
     * @see FileHelper::parseSerializedFile()
     */
    public function test_parseSerializedFile_fileNotExists() : void
    {
        $file = $this->assetsFolder . '/unknown.ser';

        $this->expectException(FileHelper_Exception::class);

        FileHelper::parseSerializedFile($file);
    }

    /**
     * @see FileHelper::parseSerializedFile()
     */
    public function test_parseSerializedFile_fileNotUnserializable() : void
    {
        $file = $this->assetsFolder . '/serialized-broken.ser';

        $this->expectException(FileHelper_Exception::class);

        FileHelper::parseSerializedFile($file);
    }

    /**
     * @see FileHelper::cliCommandExists()
     */
    public function test_cliCommandExists() : void
    {
        $output = array();
        exec('php -v 2>&1', $output);

        $text = trim(implode(' ', $output));

        $this->assertStringContainsString('PHP', $text);

        $available = !empty($text);

        $this->assertEquals($available, FileHelper::cliCommandExists('php'));
    }

    public function test_saveFile() : void
    {
        $file = $this->assetsFolder . '/' . self::SAVE_TEST_FILE;

        FileHelper::saveFile($file, 'Hoho');

        $this->assertEquals('Hoho', file_get_contents($file));
    }

    public function test_saveFile_empty() : void
    {
        $file = $this->assetsFolder . '/' . self::SAVE_TEST_FILE;

        FileHelper::saveFile($file);

        $this->assertEquals('', file_get_contents($file));
    }

    public function test_detectEOL() : void
    {
        $tests = array(
            array(
                'label' => 'CRLF',
                'file' => 'eol-crlf.txt',
                'char' => "\r\n",
                'type' => ConvertHelper_EOL::TYPE_CRLF,
                'isCRLF' => true,
                'isLF' => false,
                'isCR' => false
            ),
            array(
                'label' => 'LF',
                'file' => 'eol-lf.txt',
                'char' => "\n",
                'type' => ConvertHelper_EOL::TYPE_LF,
                'isCRLF' => false,
                'isLF' => true,
                'isCR' => false
            ),
            array(
                'label' => 'CR',
                'file' => 'eol-cr.txt',
                'char' => "\r",
                'type' => ConvertHelper_EOL::TYPE_CR,
                'isCRLF' => false,
                'isLF' => false,
                'isCR' => true
            )
        );

        foreach ($tests as $test)
        {
            $file = $this->assetsFolder . '/' . $test['file'];

            FileHelper::saveFile($file, str_repeat($test['char'], 10));

            $result = FileHelper::detectEOLCharacter($file);

            $label = $test['label'] . ' in file ' . $test['file'];

            $this->assertInstanceof(ConvertHelper_EOL::class, $result, $label);
            $this->assertEquals($test['type'], $result->getType(), $label);
            $this->assertEquals($test['isCRLF'], $result->isCRLF(), $label);
            $this->assertEquals($test['isCR'], $result->isCR(), $label);
            $this->assertEquals($test['isLF'], $result->isLF(), $label);
        }
    }

    public function test_requireFolder_notExist() : void
    {
        $this->expectException(FileHelper_Exception::class);

        FileHelper::requireFolderExists(md5('/some/unknown/folder'));
    }

    public function test_requireFolder_notAFolder() : void
    {
        $this->expectException(FileHelper_Exception::class);

        FileHelper::requireFolderExists($this->assetsFolder . '/single-line.txt');
    }

    public function test_requireFolder_pathNormalized() : void
    {
        $folder = realpath($this->assetsFolder . '/FileFinder');

        $this->assertIsString($folder);

        $normalized = FileHelper::requireFolderExists($folder);

        $this->assertEquals(FileHelper::normalizePath($folder), $normalized);
    }
}
