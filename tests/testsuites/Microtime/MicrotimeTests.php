<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites\Microtime;

use AppUtils\Microtime;
use AppUtils\Microtime\DateFormatChars;
use AppUtils\Microtime\TimeZones\NamedTimeZoneInfo;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class MicrotimeTests extends TestCase
{
    public function test_getMicroseconds() : void
    {
        $time = new Microtime('2021-06-30 14:05:11.4444');

        $this->assertEquals('2021-06-30 14:05:11.444400', $time->getISODate());
        $this->assertSame(444400, $time->getMicroseconds());

        $time = new Microtime('2021-06-30 14:05:11');

        $this->assertEquals('2021-06-30 14:05:11.000000', $time->getISODate());
        $this->assertSame(0, $time->getMicroseconds());
    }

    public function test_emptyTimezoneMustUseUTC() : void
    {
        $time = new Microtime('2023-10-01T11:45:00.666666777');

        $this->assertEquals('2023-10-01 11:45:00.666666', $time->getISODate());
        $this->assertSame(666666, $time->getMicroseconds());
        $this->assertSame('UTC', $time->getTimezone()->getName());
        $this->assertSame('+00:00', $time->getTimezoneInfo()->toOffsetString());
    }

    public function test_specificTimezone() : void
    {
        $time = new Microtime('2022-02-16T18:36:14.666666777+05:30');

        $this->assertEquals('2022-02-16 18:36:14.666666', $time->getISODate());
        $this->assertSame(666666, $time->getMicroseconds());
    }

    /**
     * Even if a timezone has been specified when creating
     * the Microtime instance, the timezone offset contained
     * in the date must take precedence.
     */
    public function test_specificTimezoneOverwritesSpecifiedZone() : void
    {
        $customZone = new DateTimeZone('Europe/Paris');
        $time = new Microtime('2022-02-16T18:36:14.666666777+05:30', $customZone);

        $this->assertSame('+05:30', $time->getTimezone()->getName());
        $this->assertSame('Asia/Kolkata', $time->getTimezoneInfo()->getAnyName());
    }

    /**
     * Importing the date back into a fresh microtime
     * instance must retain the microseconds.
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
        $date = Microtime::createFromString('2022-12-22T09:06:21.666666777Z');

        $this->assertSame('2022-12-22', $date->format('Y-m-d'));
        $this->assertSame('09:06:21', $date->format('H:i:s'));
        $this->assertSame(666666, $date->getMicroseconds());
        $this->assertSame(666, $date->getMilliseconds());
        $this->assertSame(777, $date->getNanoseconds());
    }

    public function test_dateTimeZone() : void
    {
        $date = Microtime::createFromString('2023-01-06T14:45:25.666666777 Europe/Paris');

        $info = $date->getTimezoneInfo();
        $this->assertInstanceOf(NamedTimeZoneInfo::class, $info);
        $this->assertSame('Europe/Paris', $date->getTimezoneInfo()->getName());

        $tz = $date->getTimezone();
        $this->assertSame('Europe/Paris', $tz->getName());
    }

    public function test_customFormatWithTimeZoneName() : void
    {
        $microtime = new Microtime('2023-10-01 Europe/Paris');

        $info = $microtime->getTimezoneInfo();
        $this->assertSame('Europe/Paris', $info->getAnyName());
    }

    public function test_timeZoneCaseInsensitive() : void
    {
        $microtime = new Microtime('2023-10-01 europe/PARIS');

        $info = $microtime->getTimezoneInfo();
        $this->assertSame('Europe/Paris', $info->getAnyName());
    }

    public function test_UTCCaseInsensitive() : void
    {
        $microtime = new Microtime('2023-10-01 utc');

        $info = $microtime->getTimezoneInfo();
        $this->assertSame('UTC', $info->getAnyName());
    }

    public function test_GMTCaseInsensitive() : void
    {
        $microtime = new Microtime('2023-10-01 gmt');

        $info = $microtime->getTimezoneInfo();
        $this->assertSame('UTC', $info->getAnyName());
    }

    public function test_createNow() : void
    {
        $now = Microtime::createNow();

        $this->assertInstanceOf(Microtime::class, $now);
    }

    public function test_createFromMicrotime() : void
    {
        $micro = Microtime::createFromString('1975-02-07 14:45:12.5555');

        $new = Microtime::createFromDate($micro);

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
        $date = Microtime::createFromString('1975-02-07 14:45:12.666666777');

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
        $this->assertSame(666666, $date->getMicroseconds());
        $this->assertSame(666, $date->getMilliseconds());
        $this->assertSame(777, $date->getNanoseconds());
    }

    public function test_formatWithNanoseconds() : void
    {
        $date = Microtime::createFromString('1975-02-07 14:45:12.666666777');

        $this->assertSame('666666777', $date->format(DateFormatChars::TIME_MICROSECONDS.DateFormatChars::TIME_NANOSECONDS));
    }

    public function test_copy() : void
    {
        $date = Microtime::createFromString('1975-02-07 14:45:12.333666999');
        $copy = Microtime::createFromDate($date);

        $this->assertNotSame($date, $copy);
        $this->assertSame($date->getNanoDate(), $copy->getNanoDate());
    }

    public function test_clone() : void
    {
        $date = Microtime::createFromString('1975-02-07 14:45:12.333666999');
        $copy = clone $date;

        $this->assertSame($date->getNanoDate(), $copy->getNanoDate());
    }

    public function test_getISODateWithTimeZone() : void
    {
        $date = Microtime::createFromString('1975-02-07 14:45:12.333666999', new DateTimeZone('Europe/Paris'));

        $formatted = $date->getISODate(true);
        $this->assertSame('1975-02-07T14:45:12.333666 Europe/Paris', $formatted);

        $restored = Microtime::createFromString($formatted);
        $this->assertSame('Europe/Paris', $restored->getTimezone()->getName());
    }

    public function test_getNanoDateWithTimeZone() : void
    {
        $date = Microtime::createFromString('1975-02-07 14:45:12.333666999', new DateTimeZone('Europe/Paris'));

        $formatted = $date->getNanoDate(true);
        $this->assertSame('1975-02-07T14:45:12.333666999 Europe/Paris', $formatted);

        $restored = Microtime::createFromString($formatted);
        $this->assertSame('Europe/Paris', $restored->getTimezone()->getName());
        $this->assertSame(999, $restored->getNanoseconds());
    }
}
