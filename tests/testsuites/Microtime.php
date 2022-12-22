<?php

declare(strict_types=1);

use AppUtils\Microtime;
use PHPUnit\Framework\TestCase;

final class MicrotimeTest extends TestCase
{
    public function test_getMicroseconds() : void
    {
        $time = new Microtime('2021-06-30 14:05:11.5555');

        $this->assertEquals('2021-06-30 14:05:11.555500', $time->getISODate());
        $this->assertSame(555500, $time->getMicroseconds());

        $time = new Microtime('2021-06-30 14:05:11');

        $this->assertEquals('2021-06-30 14:05:11.000000', $time->getISODate());
        $this->assertSame(0, $time->getMicroseconds());
    }

    /**
     * Ensure that importing the ISO date back into a
     * new microtime instance correctly retains the
     * microseconds information.
     */
    public function test_importExport() : void
    {
        $time = new Microtime('2021-06-30 14:05:11.5555');

        $time2 = new Microtime($time->getISODate());

        $this->assertSame(555500, $time2->getMicroseconds());
    }

    public function test_timeZone() : void
    {
        $vanilla = new DateTime();
        $micro = new Microtime();

        $this->assertSame($vanilla->getTimezone()->getName(), $micro->getTimezone()->getName());
        $this->assertSame($vanilla->format('Y-m-d H:i:s'), $micro->format('Y-m-d H:i:s'));
    }

    public function test_ISO8601() : void
    {
        $date = Microtime::createFromString('2022-12-22T09:06:21.366976535Z');

        $this->assertSame('2022-12-22', $date->format('Y-m-d'));
        $this->assertSame('09:06:21', $date->format('H:i:s'));
        $this->assertSame(366976, $date->getMicroseconds());
    }

    public function test_createNow() : void
    {
        $now = Microtime::createNow();

        $this->assertInstanceOf(Microtime::class, $now);
    }

    public function test_createFromMicrotime() : void
    {
        $micro = Microtime::createFromString('1975-02-07 14:45:12.5555');

        $new = Microtime::createFromMicrotime($micro);

        $this->assertNotSame($micro, $new);
        $this->assertEquals(1975, $new->getYear());
        $this->assertEquals(555500, $new->getMicroseconds());
    }

    public function test_createFromDateTime() : void
    {
        $date = new DateTime('1975-02-07 14:45:12.5555');

        $micro = Microtime::createFromDate($date);

        $this->assertEquals(1975, $micro->getYear());
        $this->assertEquals(555500, $micro->getMicroseconds());
    }

    public function test_timeMethods() : void
    {
        $date = Microtime::createFromString('1975-02-07 14:45:12.5555');

        $this->assertSame('pm', $date->getMeridiem());
        $this->assertTrue($date->isPM());
        $this->assertFalse($date->isAM());
        $this->assertSame(1975, $date->getYear());
        $this->assertSame(2, $date->getMonth());
        $this->assertSame(7, $date->getDay());
        $this->assertSame(14, $date->getHour24());
        $this->assertSame(2, $date->getHour12());
        $this->assertSame(45, $date->getMinutes());
        $this->assertSame(12, $date->getSeconds());
        $this->assertSame(555500, $date->getMicroseconds());
    }
}
