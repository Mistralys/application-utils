<?php

declare(strict_types=1);

namespace AppUtils;

use DateTime;

class ConvertHelper_Date
{
    /**
     * @var array<int,string[]>
     */
    protected static $months = array();

    /**
     * @var string[]
     */
    protected static $days = array();

    /**
     * @var string[]
     */
    protected static $daysShort = array();

    /**
     * @var string[]
     */
    protected static $daysInvariant = array(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    );

    /**
     * Converts a date to the corresponding day name.
     *
     * @param DateTime $date
     * @param bool $short
     * @return string|NULL
     */
    public static function toDayName(DateTime $date, bool $short=false) : ?string
    {
        $day = $date->format('l');
        $invariant = self::getDayNamesInvariant();

        $idx = array_search($day, $invariant);
        if($idx !== false) {
            $localized = self::getDayNames($short);
            return $localized[$idx];
        }

        return null;
    }

    /**
     * Retrieves a list of english day names.
     * @return string[]
     */
    public static function getDayNamesInvariant() : array
    {
        return self::$daysInvariant;
    }

    /**
     * Retrieves the day names list for the current locale.
     *
     * @param bool $short
     * @return string[]
     */
    public static function getDayNames(bool $short=false) : array
    {
        self::initDays();

        if($short) {
            return self::$daysShort;
        }

        return self::$days;
    }

    /**
     * Transforms a date into a generic human-readable date, optionally with time.
     * If the year is the same as the current one, it is omitted.
     *
     * - 6 Jan 2012
     * - 12 Dec 2012 17:45
     * - 5 Aug
     *
     * @param DateTime $date
     * @param bool $includeTime
     * @param bool $shortMonth
     * @return string
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER
     */
    public static function toListLabel(DateTime $date, bool $includeTime = false, bool $shortMonth = false) : string
    {
        $today = new DateTime();
        if($date->format('d.m.Y') === $today->format('d.m.Y'))
        {
            $label = t('Today');
        }
        else
        {
            $label = $date->format('d') . '. ' . self::month2string((int)$date->format('m'), $shortMonth) . ' ';

            if ($date->format('Y') != date('Y'))
            {
                $label .= $date->format('Y');
            }
        }

        $toolTipDateFormat = 'd.m.Y';

        if ($includeTime)
        {
            $label .= $date->format(' H:i');
            $toolTipDateFormat .= ' H:i';
        }

        return
            '<span title="'.$date->format($toolTipDateFormat).'">'.
                trim($label).
            '</span>';
    }

    /**
     * Returns a human-readable month name given the month number. Can optionally
     * return the shorthand version of the month. Translated into the current
     * application locale.
     *
     * @param int|string $monthNr
     * @param boolean $short
     * @return string
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER
     */
    public static function month2string($monthNr, bool $short = false) : string
    {
        self::initMonths();

        $monthNr = intval($monthNr);
        if (!isset(self::$months[$monthNr]))
        {
            throw new ConvertHelper_Exception(
                'Invalid month number',
                sprintf('%1$s is not a valid month number.', $monthNr),
                ConvertHelper::ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER
            );
        }

        if ($short) {
            return self::$months[$monthNr][1];
        }

        return self::$months[$monthNr][0];
    }

    /**
     * Converts a DateTime object to a timestamp, which
     * is PHP 5.2 compatible.
     *
     * @param DateTime $date
     * @return integer
     */
    public static function toTimestamp(DateTime $date) : int
    {
        return (int)$date->format('U');
    }
    /**
     * Converts a timestamp into a DateTime instance.
     *
     * @param int $timestamp
     * @return DateTime
     */
    public static function fromTimestamp(int $timestamp) : DateTime
    {
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        return $date;
    }


    private static function initMonths() : void
    {
        if (!empty(self::$months))
        {
            return;
        }

        self::$months = array(
            1 => array(t('January'), t('Jan')),
            2 => array(t('February'), t('Feb')),
            3 => array(t('March'), t('Mar')),
            4 => array(t('April'), t('Apr')),
            5 => array(t('May'), t('May')),
            6 => array(t('June'), t('Jun')),
            7 => array(t('July'), t('Jul')),
            8 => array(t('August'), t('Aug')),
            9 => array(t('September'), t('Sep')),
            10 => array(t('October'), t('Oct')),
            11 => array(t('November'), t('Nov')),
            12 => array(t('December'), t('Dec'))
        );
    }

    private static function initDays() : void
    {
        if(!empty(self::$daysShort))
        {
            return;
        }

        self::$daysShort = array(
            t('Mon'),
            t('Tue'),
            t('Wed'),
            t('Thu'),
            t('Fri'),
            t('Sat'),
            t('Sun')
        );

        self::$days = array(
            t('Monday'),
            t('Tuesday'),
            t('Wednesday'),
            t('Thursday'),
            t('Friday'),
            t('Saturday'),
            t('Sunday')
        );
    }
}
