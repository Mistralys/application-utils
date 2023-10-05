<?php
/**
 * @package Application Utils
 * @subpackage Microtime
 * @see \AppUtils\Microtime\TimeZones\TimeZoneInfo
 */

declare(strict_types=1);

namespace AppUtils\Microtime\TimeZones;

use AppUtils\Interface_Stringable;
use AppUtils\Microtime_Exception;
use DateTimeZone;

/**
 * Utility class used to store information on a
 * time zone in a date string.
 *
 * Supports notations like:
 *
 * - Empty string (UTC)
 * - Z (UTC)
 * - +00:00 (UTC)
 * - -04:30 (Venezuela/Caracas)
 * - +0200 (No colon, PHP style)
 * - UTC
 * - GMT
 * - Europe/Paris
 *
 * Using an offset value:
 *
 * <pre>
 * $offset = TimeZoneOffset::create('+01:00');
 * </pre>
 *
 * Using a time zone name:
 *
 * <pre>
 * $zone = TimeZoneOffset::create('Europe/Paris');
 * </pre>
 *
 * Using an existing {@see DateTimeZone} instance:
 *
 * <pre>
 * $native = new DateTimeZone(date_default_timezone_get());
 * $zone = TimeZoneOffset::create($native);
 * </pre>
 *
 * NOTE: This doesn't extend DateTimeZone on purpose,
 * because it isn't a replacement for it. It is only
 * used to access additional information.
 *
 * @package Application Utils
 * @subpackage Microtime
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see OffsetParser
 */
class TimeZoneInfo implements Interface_Stringable
{
    private string $name;
    private int $value;
    private string $sign = '+';

    /**
     * @var array<string,TimeZoneInfo>
     */
    private static array $instances = array();

    /**
     * @var array<string,TimeZoneInfo|NamedTimeZoneInfo>
     */
    private static array $valueInstances = array();

    /**
     * @var array<string,NamedTimeZoneInfo>
     */
    private static array $namedInstances = array();

    private function __construct(int $value, string $name)
    {
        $this->name = $name;
        $this->value = abs($value);

        if($value < 0) {
            $this->sign = '-';
        }
    }

    protected ?DateTimeZone $dateTimeZone = null;

    private static function dateTimeZoneToString(DateTimeZone $offset) : string
    {
        $name = $offset->getName();

        // Europe/Paris notation
        if(strpos($name, '/') !== false) {
            return $name;
        }

        if($name[0] === '+' || $name[0] === '-') {
            return $name;
        }

        if($name === 'UTC') {
            return 'Z';
        }

        return '';
    }

    public function getDateTimeZone() : DateTimeZone
    {
        if(!isset($this->dateTimeZone)) {
            $this->dateTimeZone = new DateTimeZone($this->toOffsetString());
        }

        return $this->dateTimeZone;
    }

    /**
     * Clears the internal cache of time zone info instances.
     * This is used in unit testing, and can have uses to
     * free memory in production code.
     *
     * @return void
     */
    public static function clearInstanceCache() : void
    {
        self::$instances = array();
        self::$valueInstances = array();
        self::$namedInstances = array();
    }

    /**
     * Creates a singleton instance for the specified offset.
     * Different notations for the same offset will return the
     * same instance.
     *
     * For example, the offsets "+1" and "+01:00" will return
     * the same instance. "Z", "+00:00" and "" (empty string)
     * will also return the same instance.
     *
     * A time zone name like "Europe/Paris" can also be specified,
     * which will return a {@see NamedTimeZoneInfo} instance.
     * Also see {@see self::createFromName()} for a better return
     * type in these cases.
     *
     * @param string|DateTimeZone|NULL $offset
     * @return TimeZoneInfo|NamedTimeZoneInfo
     * @throws Microtime_Exception {@see OffsetParser::ERROR_UNKNOWN_TIMEZONE_OFFSET_VALUE}, {@see OffsetParser::ERROR_UNRECOGNIZED_TIMEZONE_OFFSET}
     */
    public static function create($offset) : TimeZoneInfo
    {
        if($offset instanceof DateTimeZone) {
            $offset = self::dateTimeZoneToString($offset);
        }

        $offset = (string)$offset;

        if(isset(self::$instances[$offset])) {
            return self::$instances[$offset];
        }

        $parser = new OffsetParser($offset);

        if($parser->isNamed())
        {
            $instance = new NamedTimeZoneInfo($parser->getValue(), $parser->getName());
            $key = 'name-'.$parser->getName();
        }
        else
        {
            $instance = new TimeZoneInfo($parser->getValue(), $parser->getName());
            $key = 'value-'.$parser->getValue();
        }

        if(!isset(self::$valueInstances[$key])) {
            self::$valueInstances[$key] = $instance;
        }

        self::$instances[$offset] = self::$valueInstances[$key];

        return self::$instances[$offset];
    }

    /**
     * Creates a singleton instance for the specified time zone
     * name/identifier, e.g. "Europe/Paris".
     *
     * This differs from {@see self::create()} in that it
     * guarantees a {@see NamedTimeZoneInfo} instance to be
     * returned.
     *
     * @param string $name Time zone name, e.g. "Europe/Paris" (case insensitive)
     * @return NamedTimeZoneInfo
     * @throws Microtime_Exception
     */
    public static function createFromName(string $name) : NamedTimeZoneInfo
    {
        if(isset(self::$namedInstances[$name])) {
            return self::$namedInstances[$name];
        }

        $instance = self::create($name);

        if($instance instanceof NamedTimeZoneInfo) {
            self::$namedInstances[$name] = $instance;
            return $instance;
        }

        throw new Microtime_Exception(
            'Not a named time zone offset.',
            sprintf(
                'The offset [%s] is not a named time zone offset, it is a value offset.',
                $name
            ),
            OffsetParser::ERROR_UNKNOWN_TIMEZONE_NAME
        );
    }

    /**
     * Fetches a time zone offset name/identifier that it
     * can be recognized by. Because many names can have
     * the same offset, this method will return any of the
     * possible names.
     *
     * NOTE: To guarantee that the returned name is always
     * the expected one, use
     *
     * @return string A name/identifier, e.g. "Europe/Paris"
     */
    public function getAnyName() : string
    {
        return $this->name;
    }

    /**
     * @return int The offset value in seconds, e.g., 3600 for "+01:00". Negative or positive depending on the offset.
     * @see self::getTotalSeconds()
     */
    public function getOffsetValue() : int
    {
        if($this->isNegative()) {
            return $this->getTotalSeconds() * -1;
        }

        return $this->getTotalSeconds();
    }

    /**
     * @return int Absolute value, even if the offset is negative.
     * @see self::getOffsetValue()
     */
    public function getTotalSeconds() : int
    {
        return $this->value;
    }

    public function isPositive() : bool
    {
        return $this->sign === '+';
    }

    public function isNegative() : bool
    {
        return $this->sign === '-';
    }

    /**
     * @return int Absolute value, even if the offset is negative.
     */
    public function getHours() : int
    {
        return (int)($this->value / 60 / 60);
    }

    /**
     * @return int Absolute value, even if the offset is negative.
     */
    public function getMinutes() : int
    {
        return (int)($this->value / 60) % 60;
    }

    /**
     * Retrieves the offset as a string, e.g. "+01:00".
     * @return string
     */
    public function toOffsetString() : string
    {
        return (string)$this;
    }

    public function __toString() : string
    {
        return sprintf(
            '%s%02d:%02d',
            $this->sign,
            $this->getHours(),
            $this->getMinutes()
        );
    }

    /**
     * @return string The offset's sign, either "+" or "-".
     */
    public function getOffsetSign() : string
    {
        return $this->sign;
    }

    /**
     * @return array{name:string,value:int,hours:int,minutes:int,seconds:int,sign:string,negative:bool}
     */
    public function toArray() : array
    {
        return array(
            'name' => $this->getAnyName(),
            'value' => $this->getOffsetValue(),
            'hours' => $this->getHours(),
            'minutes' => $this->getMinutes(),
            'seconds' => $this->getTotalSeconds(),
            'sign' => $this->getOffsetSign(),
            'negative' => $this->isNegative(),
            'offset' => $this->toOffsetString()
        );
    }
}
