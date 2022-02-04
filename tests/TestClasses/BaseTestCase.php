<?php

declare(strict_types=1);

namespace TestClasses;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected function skipWebserverURL() : void
    {
        if(!defined('TESTS_WEBSERVER_URL'))
        {
            $this->markTestSkipped('Webserver URL has not been set.');
        }
    }
}
