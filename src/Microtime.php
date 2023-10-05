<?php
/**
 * File containing the class {@see \AppUtils\Microtime}.
 *
 * @package Application Utils
 * @subpackage Microtime
 * @see \AppUtils\Microtime
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\Microtime\DateFormatChars;
use AppUtils\Microtime\DateParseResult;
use AppUtils\Microtime\TimeZones\NamedTimeZoneInfo;
use AppUtils\Microtime\TimeZones\TimeZoneInfo;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Microtime class that extends the vanilla `DateTime` object
 * with the capability to handle microseconds, as well as to
 * add a number of utility methods.
 *
 * @package Application Utils
 * @subpackage Microtime
 * @see https://www.php.net/manual/en/datetime.format.php
 */
class Microtime extends DateTime implements Interface_Stringable
{
    public const ERROR_FAILED_CREATING_DATE_OBJECT = 88601;
    public const ERROR_FAILED_CONVERTING_STRING = 88602;
    public const ERROR_INVALID_DATE_VALUE = 88603;

    public const DATETIME_NOW = 'now';
    public const FORMAT_ISO = 'Y-m-d H:i:s.u';
    private DateParseResult $parseResult;

    /**
     * Attempts to determine the kind of date to create dynamically.
     * If you already know what type of date to create, use the `createXXX()`
     * methods instead, which perform slightly better.
     *
     * @param string|DateTime|Microtime|DateParseResult|mixed $datetime
     * @param DateTimeZone|null $timeZone
     * @throws Microtime_Exception
     *
     * @see Microtime::ERROR_FAILED_CREATING_DATE_OBJECT
     * @see Microtime::ERROR_FAILED_CONVERTING_STRING
     *
     * @see Microtime::createFromDate()
     * @see Microtime::createFromMicrotime()
     * @see Microtime::createFromString()
     * @see Microtime::createNow()
     */
    public function __construct($datetime=self::DATETIME_NOW, ?DateTimeZone $timeZone=null)
    {
        if($datetime instanceof DateParseResult)
        {
            $parsed = $datetime;
        }
        else
        {
            $parsed = $this->parseDate($datetime, $timeZone);
        }

        try
        {
            $this->parseResult = $parsed;

            parent::__construct($parsed->getDateTime(), $parsed->getTimeZone());
        }
        catch (Exception $e)
        {
            throw new Microtime_Exception(
                'Failed to create date from string.',
                sprintf(
                    'Source date string: [%s].',
                    $datetime
                ),
                self::ERROR_FAILED_CONVERTING_STRING,
                $e
            );
        }
    }

    /**
     * @return TimeZoneInfo|NamedTimeZoneInfo
     */
    public function getTimezoneInfo() : TimeZoneInfo
    {
        $parsed = $this->parseResult->getTimeZoneInfo();

        if($parsed !== null) {
            return $parsed;
        }

        return TimeZoneInfo::create($this->getTimezone());
    }

    /**
     * @param string|DateTime|Microtime|mixed $datetime
     * @param DateTimeZone|null $timeZone
     * @return DateParseResult
     * @throws Microtime_Exception
     */
    private function parseDate($datetime, ?DateTimeZone $timeZone=null) : DateParseResult
    {
        if($datetime instanceof self)
        {
            return new DateParseResult(
                $datetime->getISODate(),
                $datetime->getTimezone()
            );
        }

        if($datetime instanceof DateTime)
        {
            return new DateParseResult(
                $datetime->format(self::FORMAT_ISO),
                $datetime->getTimezone()
            );
        }

        if(empty($datetime) || $datetime === self::DATETIME_NOW)
        {
            return self::parseNow($timeZone);
        }

        if(is_string($datetime))
        {
            return new DateParseResult(
                $datetime,
                $timeZone
            );
        }

        throw new Microtime_Exception(
            'Invalid date time value',
            sprintf(
                'The specified value is not a supported date value: [%s].',
                parseVariable($datetime)->enableType()->toString()
            ),
            self::ERROR_INVALID_DATE_VALUE
        );
    }

    /**
     * @param DateTimeZone|NULL $timeZone
     * @return DateParseResult
     * @throws Microtime_Exception
     */
    private static function parseNow(?DateTimeZone $timeZone) : DateParseResult
    {
        $dateObj = DateTime::createFromFormat('0.u00 U', microtime(), new DateTimeZone('America/Denver'));

        if($timeZone === null) {
            $timeZone = new DateTimeZone(date_default_timezone_get());
        }

        if($dateObj !== false)
        {
            $dateObj->setTimezone($timeZone);

            return new DateParseResult(
                $dateObj->format(self::FORMAT_ISO),
                $timeZone
            );
        }

        throw new Microtime_Exception(
            'Failed to create microseconds date.',
            '',
            self::ERROR_FAILED_CREATING_DATE_OBJECT
        );
    }

    /**
     * Creates a new Microtime for the current time.
     *
     * @param DateTimeZone|null $timeZone
     * @return Microtime
     * @throws Microtime_Exception
     */
    public static function createNow(?DateTimeZone $timeZone=null) : Microtime
    {
        return new Microtime(self::parseNow($timeZone));
    }

    /**
     * Creates a microtime from a date string. For the microseconds
     * to be used, the string must be in a supported format.
     *
     * @param string $date
     * @param DateTimeZone|null $timeZone
     * @return Microtime
     * @throws Microtime_Exception
     */
    public static function createFromString(string $date, ?DateTimeZone $timeZone=null) : Microtime
    {
        return new Microtime(new DateParseResult($date, $timeZone));
    }

    /**
     * Creates a new Microtime instance given an existing Microtime instance.
     * The time zone is inherited.
     *
     * @param Microtime $date
     * @return Microtime
     * @throws Microtime_Exception
     */
    public static function createFromMicrotime(Microtime $date) : Microtime
    {
        return new Microtime(new DateParseResult($date->getISODate(), $date->getTimezone()));
    }

    /**
     * Creates a microtime instance from an existing DateTime instance.
     * The Microtime inherits the time zone.
     *
     * @param DateTime $date
     * @return Microtime
     * @throws Microtime_Exception
     */
    public static function createFromDate(DateTime $date) : Microtime
    {
        return new Microtime(new DateParseResult($date->format(self::FORMAT_ISO), $date->getTimezone()));
    }

    /**
     * Gets the Microseconds part of the date.
     * @return int Six-digit microseconds value.
     */
    public function getMicroseconds() : int
    {
        return (int)$this->format(DateFormatChars::TIME_MICROSECONDS);
    }

    /**
     * Gets only the milliseconds, if any. Add this
     * to the microseconds to get the full millisecond.
     *
     * @return int
     */
    public function getMilliseconds() : int
    {
        return $this->parseResult->getMilliseconds();
    }

    /**
     * ISO formatted date with microseconds, in the
     * format `Y-m-d H:i:s.u`.
     *
     * @return string
     */
    public function getISODate() : string
    {
        return $this->format(self::FORMAT_ISO);
    }

    /**
     * Date formatted for storing in a MySQL database column.
     *
     * NOTE: To store microseconds in MySQL, a DateTime column
     * needs to be used, with a length of 6 (3 for the milliseconds,
     * +3 for the microseconds). Without the length specified,
     * the milliseconds information will be stripped out.
     *
     * @return string
     */
    public function getMySQLDate() : string
    {
        return $this->getISODate();
    }

    public function __toString()
    {
        return $this->getISODate();
    }

    public function getYear() : int
    {
        return (int)$this->format(DateFormatChars::YEAR);
    }

    public function getMonth() : int
    {
        return (int)$this->format(DateFormatChars::MONTH);
    }

    public function getDay() : int
    {
        return (int)$this->format(DateFormatChars::DAY_OF_MONTH);
    }

    public function getHour24() : int
    {
        return (int)$this->format(DateFormatChars::TIME_24_ZB);
    }

    public function getHour12() : int
    {
        return (int)$this->format(DateFormatChars::TIME_12);
    }

    public function isAM() : bool
    {
        return $this->getMeridiem() === 'am';
    }

    public function isPM() : bool
    {
        return $this->getMeridiem() === 'pm';
    }

    /**
     * String identifying whether the time is ante meridiem (AM) or post meridiem (PM).
     * @return string `am` or `pm`
     */
    public function getMeridiem() : string
    {
        return $this->format(DateFormatChars::TIME_MERIDIEM_LOWER);
    }

    public function getMinutes() : int
    {
        return (int)$this->format(DateFormatChars::TIME_MINUTES_LZ);
    }

    public function getSeconds() : int
    {
        return (int)$this->format(DateFormatChars::TIME_SECONDS_LZ);
    }
}
