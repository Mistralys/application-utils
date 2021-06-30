<?php

declare(strict_types=1);

namespace AppUtils;

use DateTime;
use DateTimeZone;
use Exception;

class Microtime extends DateTime implements Interface_Stringable
{
    const ERROR_FAILED_CREATING_DATE_OBJECT = 88601;
    const ERROR_FAILED_CONVERTING_STRING = 88602;

    const FORMAT_ISO = 'Y-m-d H:i:s.u';

    /**
     * @var string
     */
    private static $defaultTimeZone = 'Europe/Paris';

    /**
     * @param string $datetime
     * @param DateTimeZone|null $timeZone
     * @throws ConvertHelper_Exception
     *
     * @see Microtime::ERROR_FAILED_CREATING_DATE_OBJECT
     * @see Microtime::ERROR_FAILED_CONVERTING_STRING
     */
    public function __construct($datetime='now', DateTimeZone $timeZone=null)
    {
        if($timeZone === null) {
            $timeZone = new DateTimeZone(self::$defaultTimeZone);
        }

        if(empty($datetime) || $datetime === 'now')
        {
            $dateObj = DateTime::createFromFormat('0.u00 U', microtime());

            if($dateObj === false) {
                throw new ConvertHelper_Exception(
                    'Failed to create microseconds date.',
                    '',
                    self::ERROR_FAILED_CREATING_DATE_OBJECT
                );
            }

            $datetime = $dateObj->format(self::FORMAT_ISO);
        }

        try
        {
            parent::__construct($datetime, $timeZone);
        }
        catch (Exception $e)
        {
            throw new ConvertHelper_Exception(
                'Failed to create date from string.',
                sprintf(
                    'Source date string: [%s].',
                    strval($datetime)
                ),
                self::ERROR_FAILED_CONVERTING_STRING
            );
        }
    }

    /**
     * Sets the default time zone to use when none is specified.
     * @param string $timeZone
     */
    public static function setDefaultTimeZone(string $timeZone) : void
    {
        self::$defaultTimeZone = $timeZone;
    }

    /**
     * Gets the microseconds part of the date.
     * @return int Six-digit microseconds value.
     */
    public function getMicroseconds() : int
    {
        return intval($this->format('u'));
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
}
