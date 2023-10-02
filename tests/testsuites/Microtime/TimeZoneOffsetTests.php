<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites\Microtime;

use AppUtils\Microtime\TimeZoneOffset;
use TestClasses\BaseTestCase;

/**
 * @see TimeZoneOffset
 */
final class TimeZoneOffsetTests extends BaseTestCase
{
    public function test_offsetValuePositive() : void
    {
        $offset = new TimeZoneOffset('+05:30');

        $this->assertSame('+05:30', $offset->getAsString());
        $this->assertSame(19800, $offset->getSeconds());
        $this->assertSame(5, $offset->getHours());
        $this->assertSame(30, $offset->getMinutes());
        $this->assertSame(19800, $offset->getValue());
        $this->assertTrue($offset->isPositive());
        $this->assertFalse($offset->isNegative());
    }

    public function test_offsetValueNegative() : void
    {
        $offset = new TimeZoneOffset('-05:30');

        $this->assertSame('-05:30', $offset->getAsString());
        $this->assertSame(19800, $offset->getSeconds());
        $this->assertSame(5, $offset->getHours());
        $this->assertSame(30, $offset->getMinutes());
        $this->assertSame(-19800, $offset->getValue());
        $this->assertFalse($offset->isPositive());
        $this->assertTrue($offset->isNegative());
    }

    public function test_offsetValueSimple() : void
    {
        $offset = new TimeZoneOffset('+5');

        $this->assertSame('+05:00', $offset->getAsString());
        $this->assertSame(18000, $offset->getSeconds());
    }

    public function test_getNames() : void
    {
        $this->assertSame(
            'Europe/Paris',
            (new TimeZoneOffset('+1'))->getName()
        );

        $this->assertSame(
            'Europe/Helsinki',
            (new TimeZoneOffset('+2'))->getName()
        );

        $this->assertSame(
            'Atlantic/Azores',
            (new TimeZoneOffset('-1'))->getName()
        );

        $this->assertSame(
            'America/Sao_Paulo',
            (new TimeZoneOffset('-3'))->getName()
        );
    }
}
