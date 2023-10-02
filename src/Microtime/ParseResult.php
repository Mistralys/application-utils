<?php
/**
 * File containing the class {@see \AppUtils\Microtime_ParseResult}.
 *
 * @package Application Utils
 * @subpackage Microtime
 * @see \AppUtils\Microtime_ParseResult
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\Microtime\TimeZoneOffset;
use DateTimeZone;

/**
 * Date parsing result, containing the date string
 * and time zone to use for the DateTime constructor.
 *
 * This is used to simplify creating a new microtime
 * instance when using the factory methods, to avoid
 * the type checks that are done when using the
 * constructor.
 *
 * @package Application Utils
 * @subpackage Microtime
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Microtime_ParseResult implements Interface_Stringable
{
    private string $dateTime;
    private DateTimeZone $timeZone;
    private TimeZoneOffset $timeZoneOffset;

    public function __construct(string $datetime, DateTimeZone $timeZone)
    {
        $this->dateTime = $datetime;
        $this->timeZone = $timeZone;

        if(stripos($datetime, 'T') !== false) {
            $this->parseISO8601($datetime);
        }
    }

    public function getTimeZoneOffset() : TimeZoneOffset
    {
        if(!isset($this->timeZoneOffset)) {
            $this->timeZoneOffset = new TimeZoneOffset('Z');
        }

        return $this->timeZoneOffset;
    }

    private function parseISO8601(string $datetime) : void
    {
        preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})T([0-9]{2}:[0-9]{2}:[0-9]{2})\.([0-9]+)(.*)/', $datetime, $matches);

        if(empty($matches[0])) {
            return;
        }

        $this->timeZoneOffset = new TimeZoneOffset(trim($matches[4]));

        $this->timeZone = new DateTimeZone(sprintf(
            '%s%02d%02d',
            $this->timeZoneOffset->getSign(),
            $this->timeZoneOffset->getHours(),
            $this->timeZoneOffset->getMinutes()
        ));

        $this->dateTime = sprintf(
            '%s %s.%s%s',
            $matches[1],
            $matches[2],
            substr($matches[3], 0, 6),
            $this->timeZoneOffset
        );
    }

    public function __toString() : string
    {
        return $this->getDateTime();
    }

    /**
     * @return string
     */
    public function getDateTime() : string
    {
        return $this->dateTime;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimeZone() : DateTimeZone
    {
        return $this->timeZone;
    }
}
