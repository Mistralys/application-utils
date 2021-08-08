<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;
use AppUtils\OutputBuffering_Exception;
use PHPUnit\Framework\TestCase;

final class OutputBufferingTests extends TestCase
{
    public function test_getClean() : void
    {
        OutputBuffering::start();

        echo 'FooBar';

        $this->assertEquals('FooBar', OutputBuffering::get());
    }

    public function test_notStarted() : void
    {
        try
        {
            OutputBuffering::get();
        }
        catch (OutputBuffering_Exception $e)
        {
            $this->assertSame(OutputBuffering::ERROR_BUFFER_NOT_STARTED, $e->getCode());
            return;
        }

        $this->fail('No exception or wrong exception thrown.');
    }

    public function test_isActive() : void
    {
        $this->assertFalse(OutputBuffering::isActive());

        OutputBuffering::start();

        $this->assertTrue(OutputBuffering::isActive());

        OutputBuffering::get();

        $this->assertFalse(OutputBuffering::isActive());
    }
}
