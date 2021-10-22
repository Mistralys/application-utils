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
class Microtime_ParseResult
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
        $this->dateTime = $datetime;
        $this->timeZone = $timeZone;
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
