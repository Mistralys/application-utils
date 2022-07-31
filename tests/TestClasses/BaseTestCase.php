<?php

declare(strict_types=1);

namespace TestClasses;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected string $assetsRootFolder;

    protected function setUp() : void
    {
        parent::setUp();

        $this->assetsRootFolder = __DIR__.'/../assets';
    }

    protected function skipWebserverURL() : void
    {
        if(!defined('TESTS_WEBSERVER_URL'))
        {
            $this->markTestSkipped('Webserver URL has not been set.');
        }
    }
}
