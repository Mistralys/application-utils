<?php

declare(strict_types=1);

namespace FileHelperTests;

use AppUtils\FileHelper;
use TestClasses\FileHelperTestCase;

class FileDownloaderTest extends FileHelperTestCase
{
    protected const DOWNLOAD_FILE_NAME = 'download.txt';
    protected const DOWNLOAD_CONTENT_CHECK = 'Download successful.';

    public function test_download() : void
    {
        $this->skipWebserverURL();

        $content = FileHelper::downloadFile(TESTS_WEBSERVER_URL.'/assets/FileHelper/'.self::DOWNLOAD_FILE_NAME);

        $this->assertStringContainsString(self::DOWNLOAD_CONTENT_CHECK, $content);
    }

    public function test_downloadFailure() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_CANNOT_OPEN_URL);

        FileHelper::downloadFile(
            'https://nowhere-'.md5((string)microtime(true)).'.systems/file.txt',
            2
        );
    }
}
