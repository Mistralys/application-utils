<?php

declare(strict_types=1);

namespace AppUtils\Microtime;

/**
 * Collection of constants for all available format characters
 * when using {@see \DateTime::format()}.
 *
 * Naming scheme principles:
 *
 * - Numbers with leading zeroes end in `_LZ`.
 * - Zero-based numbers end in `_ZB`.
 *
 * @see https://www.php.net/manual/en/datetime.format.php
 */
class DateFormatChars
{
    // region: Months

    /**
     * Month name, full length.
     */
    public const MONTH_NAME_LONG = 'F';

    /**
     * Month number with leading zeros
     */
    public const MONTH_LZ = 'm';

    /**
     * Month number without leading zeros
     */
    public const MONTH = 'n';

    /**
     * Month name, three-letter short variant.
     */
    public const MONTH_NAME_SHORT = 'M';

    /**
     * The amount of days in the month (28 through 31)
     */
    public const MONTH_DAYS = 't';

    // endregion

    // region: Days

    /**
     * Day of the month as name, `Mon` through `Sun`
     */
    public const DAY_NAME_SHORT = 'D';

    /**
     * Day of the month without leading zeros
     */
    public const DAY_OF_MONTH = 'j';

    /**
     * Day of the month with leading zeros
     */
    public const DAY_OF_MONTH_LZ = 'd';

    /**
     * `Monday` through `Saturday`
     */
    public const DAY_NAME_LONG = 'l';

    /**
     * English ordinal suffix for the day of the month (th, nd...)
     * Works well with {@see DateFormatChars::DAY_OF_MONTH}.
     */
    public const DAY_ORDINAL_SUFFIX = 'S';

    /**
     * One-based day of the week (1=Monday, 7=Sunday)
     */
    public const DAY_OF_WEEK = 'N';

    /**
     * Zero-Based day of the week (0=Sunday, 6=Saturday)
     */
    public const DAY_OF_WEEK_ZB = 'w';

    /**
     * Zero-based day of the year.
     */
    public const DAY_OF_YEAR_ZB = 'z';

    // endregion


    // region: Weeks

    /**
     * One-based week number in the year, weeks starting on Monday.
     * Example: 42 (the 42nd week in the year).
     */
    public const WEEK_OF_YEAR = 'W';

    // endregion

    // region: Year

    /**
     * `1` if it's a leap year, `0` otherwise.
     */
    public const YEAR_IS_LEAP = 'L';

    /**
     * Year number, at least 4 digits, with minus sign for years BCE.
     */
    public const YEAR = 'Y';

    /**
     * Like {@see DateFormatChars::YEAR}, except that if the week number
     * ({@see DateFormatChars::WEEK_OF_YEAR}) belongs to the previous
     * or next year, that year is used instead.
     */
    public const YEAR_WEEK_BASED = 'o';

    /**
     * Two-digit year number with leading zero.
     */
    public const YEAR_SHORT_LZ = 'y';

    // endregion

    // region: Time


    /**
     * `am` or `pm`
     */
    public const TIME_MERIDIEM_LOWER = 'a';

    /**
     * `AM` or `PM`
     */
    public const TIME_MERIDIEM_UPPER = 'A';

    /**
     * Swatch internet time.
     * Range: `000` through `999`
     */
    public const TIME_SWATCH = 'B';

    /**
     * 12-hour time, one-based, without leading zeros.
     * Range: 1 through 12
     */
    public const TIME_12 = 'g';

    /**
     * 12-hour time, one-based, with leading zeros.
     * Range: `01` through `12`
     */
    public const TIME_12_LZ = 'h';

    /**
     * 24-hour time, zero-based, without leading zeros.
     * Range: `0` through `23`
     */
    public const TIME_24_ZB = 'G';

    /**
     * 24-hour time, zero-based, with leading zeros.
     * Range: `00` through `23`
     */
    public const TIME_24_ZB_LZ = 'H';

    /**
     * Minutes with leading zeros.
     * Range: `00` to `59`
     */
    public const TIME_MINUTES_LZ = 'i';

    /**
     * Seconds with leading zeros.
     * Range: `00` to `59`
     */
    public const TIME_SECONDS_LZ = 's';

    /**
     * 6-digit microseconds, zero-based.
     * Examples: `689425`, `000042`
     */
    public const TIME_MICROSECONDS = 'u';

    /**
     * 3-digit milliseconds, zero-based.
     * Examples: `578`, `001`
     */
    public const TIME_MILLISECONDS = 'v';

    // endregion

    // region: Timezone

    /**
     * Time zone identifier, e.g. "UTC", "GMT", "Atlantic/Azores".
     */
    public const ZONE_IDENT = 'e';

    /**
     * 1 if Daylight Saving Time enabled, 0 otherwise.
     */
    public const ZONE_DAYLIGHT_SAVING = 'I';

    /**
     * Difference to Greenwich time (GMT) without colon between hours and minutes.
     * Example: `+0200`
     */
    public const ZONE_GMT = 'O';

    /**
     * Difference to Greenwich time (GMT) with colon between hours and minutes.
     * Example: `+02:00`
     */
    public const ZONE_GMT_COLON = 'P';

    /**
     * Like {@see DateFormatChars::ZONE_GMT_COLON}, but returns
     * {@see DateFormatChars::ZONE_OFFSET} instead of `+00:00`
     *
     * NOTE: Available since PHP 8.0.0
     */
    public const ZONE_GMT_OFFSET = 'p';

    /**
     * Timezone abbreviation, if known; otherwise the GMT offset.
     * Examples: `EST`, `MDT`, `+05`
     */
    public const ZONE_ABBREV = 'T';

    /**
     * Timezone offset in seconds. The offset for timezones west of
     * UTC is always negative, and for those to the east of UTC is
     * always positive.
     *
     * Range: -43200 through 50400.
     */
    public const ZONE_OFFSET = 'Z';

    // endregion

    // region: Full dates (templates)

    public const ISO8601 = 'c';
    public const RFC2822 = 'r';
    public const RFC5322 = 'r';
    public const TIMESTAMP = 'U';
}
