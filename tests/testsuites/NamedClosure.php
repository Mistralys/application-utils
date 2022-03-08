<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\NamedClosure;
use PHPUnit\Framework\TestCase;

final class NamedClosureTest extends TestCase
{
    /**
     * @var bool
     */
    private bool $callbackDone = false;

    public function test_autoOrigin() : void
    {
        $callback = array($this, 'callback_public');
        $callable = NamedClosure::fromArray($callback);

        $this->assertEquals(ConvertHelper::callback2string($callback), $callable->getOrigin());
    }

    public function test_customOrigin() : void
    {
        $callable = NamedClosure::fromClosure(Closure::fromCallable(array($this, 'callback_private')), 'Custom origin');

        $this->assertEquals('Custom origin', $callable->getOrigin());
    }

    public function test_publicMethod() : void
    {
        $this->callbackDone = false;

        $callable = NamedClosure::fromArray(array($this, 'callback_public'), $this);
        $callable();

        $this->assertTrue($this->callbackDone);
    }

    public function test_privateMethod() : void
    {
        $this->callbackDone = false;

        $closure = NamedClosure::fromClosure(Closure::fromCallable(array($this, 'callback_private')), $this);
        $closure();

        $this->assertTrue($this->callbackDone);
    }

    public function callback_public() : void
    {
        $this->callbackDone = true;
    }

    private function callback_private() : void
    {
        $this->callbackDone = true;
    }
}
