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

        // Clear all request variables for tests that
        // work with these.
        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Leave the variables in a clean state
        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }

    protected function skipWebserverURL() : void
    {
        if(!defined('TESTS_WEBSERVER_URL'))
        {
            $this->markTestSkipped('Webserver URL has not been set.');
        }
    }
}
