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
    /**
     * @var string
     */
    private $dateTime;

    /**
     * @var DateTimeZone
     */
    private $timeZone;

    public function __construct(string $datetime, DateTimeZone $timeZone)
    {
        if(stripos($datetime, 'T') !== false) {
            $datetime = $this->parseISO8601($datetime);
        }

        $this->dateTime = $datetime;
        $this->timeZone = $timeZone;
    }

    private function parseISO8601(string $datetime) : string
    {
        preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})T([0-9]{2}:[0-9]{2}:[0-9]{2})\.([0-9]+)Z/', $datetime, $matches);

        if(!empty($matches[0])) {
            return sprintf(
                '%s %s.%s',
                $matches[1],
                $matches[2],
                substr($matches[3], 0, 6)
            );
        }

        return $datetime;
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
