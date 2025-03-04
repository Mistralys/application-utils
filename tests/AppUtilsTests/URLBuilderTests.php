<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\Request;
use AppUtils\URLBuilder\URLBuilder;
use AppUtils\URLBuilder\URLBuilderException;
use TestClasses\BaseTestCase;

final class URLBuilderTests extends BaseTestCase
{
    public function test_importURLParameters() : void
    {
        $url = URLBuilder::create()->importURL(TESTS_WEBSERVER_URL.'?foo=bar&argh=lopos');

        $this->assertStringContainsString('foo=bar', (string)$url);
        $this->assertStringContainsString('argh=lopos', (string)$url);
        $this->assertSame(
            array('argh' => 'lopos', 'foo' => 'bar'),
            $url->getParams()
        );
    }

    public function test_importEmptyDispatcher() : void
    {
        // Duplicate slashes are ignored when importing the dispatcher
        $url = TESTS_WEBSERVER_URL.'/////';

        $this->assertSame('', URLBuilder::create()->importURL($url)->getDispatcher());
    }

    public function test_importURLDispatcherScript() : void
    {
        $url = TESTS_WEBSERVER_URL.'/dispatcher.php?foo=bar&argh=lopos';

        $this->assertSame('dispatcher.php', URLBuilder::create()->importURL($url)->getDispatcher());
    }

    public function test_importURLDispatcherPath() : void
    {
        $url = TESTS_WEBSERVER_URL.'/dispatcher/path/?foo=bar&argh=lopos';

        $this->assertSame('dispatcher/path/', URLBuilder::create()->importURL($url)->getDispatcher());
    }

    public function test_removeParam() : void
    {
        $params = URLBuilder::create(array('foo' => 'bar', 'argh' => 'lopos'))
            ->remove('foo')
            ->getParams();

        $this->assertSame(array('argh' => 'lopos'), $params);
    }

    public function test_otherHostsThrowAnException() : void
    {
        $this->expectExceptionCode(URLBuilderException::ERROR_INVALID_HOST);

        URLBuilder::create()->importURL('https://example.com/');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Request::getInstance()->setBaseURL(TESTS_WEBSERVER_URL);
    }
}
