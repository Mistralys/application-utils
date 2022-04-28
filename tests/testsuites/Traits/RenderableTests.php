<?php
/**
 * @package Application Utils
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\Traits;

use AppUtils\OutputBuffering;
use TestClasses\BaseTestCase;
use TestClasses\RenderableExceptionTest;
use TestClasses\RenderableTest;

/**
 * @package Application Utils
 * @subpackage UnitTests
 */
final class RenderableTests extends BaseTestCase
{
    public function test_render() : void
    {
        $renderable = new RenderableTest();

        $this->assertSame(RenderableTest::RENDERED_TEXT, $renderable->render());
        $this->assertSame(RenderableTest::RENDERED_TEXT, (string)$renderable);
    }

    public function test_display() : void
    {
        OutputBuffering::start();

        (new RenderableTest())->display();

        $this->assertSame(RenderableTest::RENDERED_TEXT, OutputBuffering::get());
    }

    /**
     * When using the magic method `__toString()`, no exceptions
     * may be called. The trait handles this with a try/catch block
     * and an error message returned instead.
     */
    public function test_exception() : void
    {
        $result = (string)(new RenderableExceptionTest());

        $this->assertStringContainsString(RenderableExceptionTest::EXCEPTION_MESSAGE, $result);
    }
}
