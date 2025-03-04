<?php
/**
 * @package Application Utils Tests
 * @subpackage URLBuilder
 */

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\Request;
use AppUtils\URLBuilder\URLBuilder;
use AppUtils\URLBuilder\URLBuilderException;
use AppUtilsTestClasses\Stubs\StubCustomURLBuilder;
use TestClasses\BaseTestCase;
use function AppUtils\parseURL;

/**
 * @package Application Utils Tests
 * @subpackage URLBuilder
 * @covers \AppUtils\URLBuilder\URLBuilder
 */
final class URLBuilderTests extends BaseTestCase
{
    // region: _Tests

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

    public function test_createFromURL() : void
    {
        $url = URLBuilder::createFromURL(TESTS_WEBSERVER_URL.'?argh=lopos');

        $this->assertStringContainsString('argh=lopos', (string)$url);
    }

    public function test_createFromURLInfo() : void
    {
        $url = URLBuilder::createFromURLInfo(parseURL(TESTS_WEBSERVER_URL.'?argh=lopos'));

        $this->assertStringContainsString('argh=lopos', (string)$url);
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

    public function test_addJSONVariable() : void
    {
        $builder = URLBuilder::create();
        $builder->arrayJSON('json', array('foo' => 'bar'));

        $this->assertStringContainsString('json=%7B%22foo%22%3A%22bar%22%7D', (string)$builder);
    }

    public function test_addBoolean() : void
    {
        $builder = URLBuilder::create();
        $builder->bool('boolTrue', true);
        $builder->bool('boolYes', true, true);

        $this->assertStringContainsString('boolTrue=true', (string)$builder);
        $this->assertStringContainsString('boolYes=yes', (string)$builder);
    }

    public function test_otherHostsThrowAnException() : void
    {
        $this->expectExceptionCode(URLBuilderException::ERROR_INVALID_HOST);

        URLBuilder::create()->importURL('https://example.com/');
    }

    public function test_emptyStringIsConsideredEmpty() : void
    {
        $builder = URLBuilder::create()->string('string', '');

        $this->assertNull($builder->getParam('string'));
        $this->assertStringNotContainsString('string=', (string)$builder);
    }

    public function test_whitespaceStringIsNotConsideredEmpty() : void
    {
        $builder = URLBuilder::create()->string('whitespace', '    ');

        $this->assertSame('    ', $builder->getParam('whitespace'));
        $this->assertStringContainsString('whitespace=++++', (string)$builder);
    }

    public function test_zeroIntegerIsNotConsideredEmpty() : void
    {
        $builder = URLBuilder::create()->int('zero', 0);

        $this->assertSame('0', $builder->getParam('zero'));
        $this->assertStringContainsString('zero=0', (string)$builder);
    }

    public function test_hasParam() : void
    {
        $builder = URLBuilder::create()
            ->string('foo', '')
            ->int('zero', 0);

        $this->assertFalse($builder->hasParam('foo'));
        $this->assertTrue($builder->hasParam('zero'));
    }

    public function test_customBuilder() : void
    {
        $builder = StubCustomURLBuilder::create();

        $this->assertInstanceOf(StubCustomURLBuilder::class, $builder);

        $builder->customParam('value');

        $this->assertStringContainsString('custom=value', (string)$builder);
    }

    public function test_inheritParam() : void
    {
        $_REQUEST['inherited'] = 'foorgh';

        $builder = URLBuilder::create()
            ->inheritParam('inherited');

        $this->assertSame('foorgh', $builder->getParam('inherited'));
    }

    // endregion

    // region: Support methods

    protected function setUp(): void
    {
        parent::setUp();

        Request::getInstance()->setBaseURL(TESTS_WEBSERVER_URL);
    }

    // endregion
}
