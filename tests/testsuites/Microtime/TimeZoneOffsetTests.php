<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites\Microtime;

use AppUtils\Microtime\TimeZones\NamedTimeZoneInfo;
use AppUtils\Microtime\TimeZones\OffsetParser;
use AppUtils\Microtime\TimeZones\TimeZoneInfo;
use DateTimeZone;
use TestClasses\BaseTestCase;

/**
 * @package Application Utils Tests
 * @subpackage Microtime
 *
 * @see TimeZoneInfo
 * @see NamedTimeZoneInfo
 * @see OffsetParser
 */
final class TimeZoneOffsetTests extends BaseTestCase
{
    // region: _Tests

    public function test_offsetValuePositive() : void
    {
        $offset = TimeZoneInfo::create('+05:30');

        $this->assertSame('+05:30', $offset->toOffsetString());
        $this->assertSame(19800, $offset->getTotalSeconds());
        $this->assertSame(5, $offset->getHours());
        $this->assertSame(30, $offset->getMinutes());
        $this->assertSame(19800, $offset->getOffsetValue());
        $this->assertTrue($offset->isPositive());
        $this->assertFalse($offset->isNegative());
    }

    public function test_offsetValueNegative() : void
    {
        $offset = TimeZoneInfo::create('-04:30');

        $this->assertSame('-04:30', $offset->toOffsetString());
        $this->assertSame(16200, $offset->getTotalSeconds());
        $this->assertSame(4, $offset->getHours());
        $this->assertSame(30, $offset->getMinutes());
        $this->assertSame(-16200, $offset->getOffsetValue());
        $this->assertFalse($offset->isPositive());
        $this->assertTrue($offset->isNegative());
        $this->assertSame('America/Caracas', $offset->getAnyName(), 'Must be Venezuela time');
    }

    public function test_offsetValueSimple() : void
    {
        $offset = TimeZoneInfo::create('+5');

        $this->assertSame('+05:00', $offset->toOffsetString());
        $this->assertSame(18000, $offset->getTotalSeconds());
    }

    public function test_offsetValueWithoutColon() : void
    {
        $offset = TimeZoneInfo::create('+0530');

        $this->assertSame('+05:30', $offset->toOffsetString());
    }

    /**
     * There are no countries or cities in the UTC-02:00 timezone:
     * It is Greenland and Antarctica, and some islands.
     */
    public function test_offsetNotExistsException() : void
    {
        $this->expectExceptionCode(OffsetParser::ERROR_UNKNOWN_TIMEZONE_OFFSET_VALUE);

        TimeZoneInfo::create('-02:00');
    }

    public function test_nameNotExistsException() : void
    {
        $this->expectExceptionCode(OffsetParser::ERROR_UNKNOWN_TIMEZONE_NAME);

        TimeZoneInfo::create('Europe/Unknown');
    }

    /**
     * These names are the ones typically returned by PHP's
     * <code>timezone_name_from_abbr</code> function for
     * the given offsets.
     */
    public function test_getDefaultNames() : void
    {
        $this->assertSame(
            'Europe/Paris',
            TimeZoneInfo::create('+1')->getAnyName()
        );

        $this->assertSame(
            'Europe/Helsinki',
            TimeZoneInfo::create('+2')->getAnyName()
        );

        $this->assertSame(
            'Atlantic/Azores',
            TimeZoneInfo::create('-1')->getAnyName()
        );

        $this->assertSame(
            'America/Sao_Paulo',
            TimeZoneInfo::create('-3')->getAnyName()
        );
    }

    public function test_nameCreatesNamedInstance() : void
    {
        $offset = TimeZoneInfo::create('Europe/Paris');

        $this->assertInstanceOf(NamedTimeZoneInfo::class, $offset);
    }

    public function test_createFromName() : void
    {
        $offset = TimeZoneInfo::createFromName('Europe/Paris');

        $this->assertSame('Europe/Paris', $offset->getName());
        $this->assertSame('Europe/Paris', $offset->getAnyName());
        $this->assertSame('+02:00', $offset->toOffsetString());
        $this->assertSame(7200, $offset->getOffsetValue());
    }

    public function test_createFromNameCaseInsensitive() : void
    {
        $offset = TimeZoneInfo::createFromName('europe/PARIS');

        $this->assertSame('Europe/Paris', $offset->getName());
    }

    public function test_createFromUTCNameInsensitive() : void
    {
        $offset = TimeZoneInfo::create('UTC');

        $this->assertSame('UTC', $offset->getAnyName());
    }

    public function test_createFromDateTimeZoneInstance() : void
    {
        $native = new DateTimeZone('Europe/Berlin');

        $zone = TimeZoneInfo::create($native);
        $this->assertInstanceOf(NamedTimeZoneInfo::class, $zone);
        $this->assertSame('Europe/Berlin', $zone->getName());
    }

    public function test_sameOffsetsAreSameInstances() : void
    {
        $this->assertSame(
            TimeZoneInfo::create('+1'),
            TimeZoneInfo::create('+01:00')
        );

        $this->assertSame(
            TimeZoneInfo::create(''),
            TimeZoneInfo::create('Z')
        );

        $this->assertSame(
            TimeZoneInfo::create(null),
            TimeZoneInfo::create('Z')
        );

        $this->assertSame(
            TimeZoneInfo::create(''),
            TimeZoneInfo::create('+00:00')
        );

        $this->assertSame(
            TimeZoneInfo::create('Europe/Paris'),
            TimeZoneInfo::create('europe/paris')
        );
    }

    public function test_offsetsAndNamesHaveDifferentClasses() : void
    {
        $this->assertInstanceOf(TimeZoneInfo::class, TimeZoneInfo::create('+02:00'));
        $this->assertInstanceOf(NamedTimeZoneInfo::class, TimeZoneInfo::create('Europe/Paris'));
    }

    public function test_getDateTimeZone() : void
    {
        $info = TimeZoneInfo::createFromName('Europe/Paris');

        $tz = $info->getDateTimeZone();
        $this->assertSame('Europe/Paris', $tz->getName());
    }

    // endregion

    // region: Support methods

    protected function setUp(): void
    {
        parent::setUp();

        TimeZoneInfo::clearInstanceCache();
    }

    // endregion
}
