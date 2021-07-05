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

        $this->assertSame($vanilla->format('Y-m-d H:i:s'), $micro->format('Y-m-d H:i:s'));
    }
}
