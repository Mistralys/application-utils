<?php

declare(strict_types=1);

namespace FileHelperTests;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper_MimeTypes;
use TestClasses\FileHelperTestCase;

final class MimeTypeTests extends FileHelperTestCase
{
    // region: _Tests

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

    public function test_fileDetectType() : void
    {
        $this->assertSame('image/jpeg', FileInfo::factory('image.jpg')->getMimeType());
    }

    public function test_fileDetectTypeCaseInsensitive() : void
    {
        $this->assertSame('image/png', FileInfo::factory('image.PNG')->getMimeType());
    }

    public function test_detectTypeCaseInsensitive() : void
    {
        $this->assertSame('image/png', FileHelper_MimeTypes::getMime('PNG'));
    }

    public function test_detectWithDot() : void
    {
        $this->assertSame('image/png', FileHelper_MimeTypes::getMime('.png'));
    }

    public function test_extensionExists() : void
    {
        $this->assertTrue(FileHelper_MimeTypes::extensionExists('png'));
        $this->assertTrue(FileHelper_MimeTypes::extensionExists('.png'));
        $this->assertTrue(FileHelper_MimeTypes::extensionExists('PNG'));

        $this->assertFalse(FileHelper_MimeTypes::extensionExists('unknown'));
    }

    public function test_setCustomMimeType() : void
    {
        $this->assertNull(FileHelper_MimeTypes::setMimeType('push', 'application/json'));

        $this->assertSame('application/json', FileHelper_MimeTypes::getMime('push'));
    }

    public function test_setMimeTypeCaseInsensitive() : void
    {
        FileHelper_MimeTypes::setMimeType('.FOO', 'image/FOO');

        $this->assertSame('image/foo', FileHelper_MimeTypes::getMime('foo'));
    }

    public function test_canBrowserDisplay() : void
    {
        $this->assertTrue(FileHelper_MimeTypes::canBrowserDisplay('html'));
        $this->assertFalse(FileHelper_MimeTypes::canBrowserDisplay('exe'));
    }

    public function test_canBrowserDisplayCaseInsensitive() : void
    {
        $this->assertTrue(FileHelper_MimeTypes::canBrowserDisplay('.HTML'));
    }

    public function test_addBrowserCanDisplay() : void
    {
        $this->assertFalse(FileHelper_MimeTypes::canBrowserDisplay('foo'));

        FileHelper_MimeTypes::setBrowserCanDisplay('foo', true);

        $this->assertTrue(FileHelper_MimeTypes::canBrowserDisplay('foo'));
    }

    public function test_removeBrowserCanDisplay() : void
    {
        $this->assertTrue(FileHelper_MimeTypes::canBrowserDisplay('txt'));

        FileHelper_MimeTypes::setBrowserCanDisplay('txt', false);

        $this->assertFalse(FileHelper_MimeTypes::canBrowserDisplay('txt'));
    }

    public function test_browserCanDisplayCaseInsensitive() : void
    {
        FileHelper_MimeTypes::setBrowserCanDisplay('.BAR', true);

        $this->assertTrue(FileHelper_MimeTypes::canBrowserDisplay('bar'));
    }

    public function test_getExtensionsByMime() : void
    {
        $this->assertSame(array('png'), FileHelper_MimeTypes::getExtensionsByMime('image/png'));
        $this->assertEmpty(FileHelper_MimeTypes::getExtensionsByMime('unknown/mime'));
    }

    public function test_getExtensionsByMimeMultipleExtensions() : void
    {
        $this->assertCount(6, FileHelper_MimeTypes::getExtensionsByMime(FileHelper\MimeTypesEnum::MIME_APPLICATION_EXCEL));
    }

    public function test_getExtensionsByMimeCaseInsensitive() : void
    {
        $this->assertSame(array('png'), FileHelper_MimeTypes::getExtensionsByMime('IMAGE/PNG'));
    }

    public function test_resetToDefaults() : void
    {
        FileHelper_MimeTypes::setMimeType('custom', 'custom/mime');

        $this->assertSame('custom/mime', FileHelper_MimeTypes::getMime('custom'));

        FileHelper_MimeTypes::resetToDefaults();

        $this->assertNull(FileHelper_MimeTypes::getMime('custom'));
    }

    // endregion

    // region: Support methods

    protected function setUp(): void
    {
        parent::setUp();

        FileHelper_MimeTypes::resetToDefaults();
    }

    // endregion
}
