<?php
/**
 * @package Application Utils
 * @subpackage Microtime
 * @see \AppUtils\Microtime\TimeZones\OffsetParser
 */

declare(strict_types=1);

namespace AppUtils\Microtime\TimeZones;

use AppUtils\Microtime_Exception;
use DateTime;
use DateTimeZone;
use Throwable;

/**
 * Parser for time zone offset values.
 *
 * - Empty string (UTC)
 * - Z (UTC)
 * - +00:00 (UTC)
 * - -04:30 (Venezuela/Caracas)
 *
 * @package Application Utils
 * @subpackage Microtime
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://www.ietf.org/rfc/rfc3339.txt The RFC for time zone offsets
 */
class OffsetParser
{
    public const ERROR_UNKNOWN_TIMEZONE_OFFSET_VALUE = 144702;
    public const ERROR_UNRECOGNIZED_TIMEZONE_OFFSET = 144701;
    public const ERROR_UNKNOWN_TIMEZONE_NAME = 144703;
    public const ERROR_DATE_TIME_EXCEPTION = 144704;

    private string $name = 'UTC';
    private int $value = 0;
    private string $sign = '+';
    private bool $isNamed = false;

    /**
     * @var array<string,array{identifier:string,offset:int}>|null
     */
    private static ?array $identifierOffsets = null;

    /**
     * @param string $offset
     * @throws Microtime_Exception
     */
    public function __construct(string $offset)
    {
        $this->parseOffset(trim($offset));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): int
    {
        if($this->sign === '-') {
            return $this->value * -1;
        }

        return $this->value;
    }

    /**
     * @param string $offset
     * @return void
     * @throws Microtime_Exception
     */
    private function parseOffset(string $offset) : void
    {
        if(empty($offset) || in_array(strtolower($offset), array('utc', 'gmt', 'z')))
        {
            return;
        }

        if($offset[0] === '+' || $offset[0] === '-')
        {
            $this->sign = $offset[0];
            $this->parseOffsetValue($offset);
            return;
        }

        if(strpos($offset, '/') !== false)
        {
            $this->parseOffsetName($offset);
            return;
        }

        throw new Microtime_Exception(
            'Unrecognized time zone offset in date.',
            sprintf(
                'The offset [%s] could not be recognized as any of the supported formats. '.
                'Examples of expected values: "" (empty string) "Z", "+00:00", "-04:30", "+5"',
                $offset
            ),
            self::ERROR_UNRECOGNIZED_TIMEZONE_OFFSET
        );
    }

    /**
     * @param string $offset
     * @return void
     * @throws Microtime_Exception
     */
    private function parseOffsetValue(string $offset) : void
    {
        $workOffset = ltrim($offset, '+-');

        if(strpos($offset, ':'))
        {
            $parts = explode(':', $workOffset);
        }
        else
        {
            $parts = array(
                substr($workOffset, 0, 2),
                substr($workOffset, 2, 2)
            );
        }

        $hour = (int)$parts[0];
        $minute = 0;

        if(isset($parts[1])) {
            $minute = (int)$parts[1];
        }

        $this->value = ($hour * 60 * 60) + ($minute * 60);

        $name = timezone_name_from_abbr("", $this->getValue(), 0);
        if($name !== false) {
            $this->name = $name;
            return;
        }

        throw new Microtime_Exception(
            'Unknown time zone offset value.',
            sprintf(
                'No time zone name found for offset value: [%s] (%s seconds).',
                $offset,
                $this->getValue()
            ),
            self::ERROR_UNKNOWN_TIMEZONE_OFFSET_VALUE
        );
    }

    /**
     * @param string $name
     * @return void
     * @throws Microtime_Exception
     */
    private function parseOffsetName(string $name) : void
    {
        $invariantName = strtolower($name);

        $offsets = self::getIdentifierOffsets();

        if(isset($offsets[$invariantName]))
        {
            $this->isNamed = true;
            $this->value = $offsets[$invariantName]['offset'];
            $this->name = $offsets[$invariantName]['identifier'];

            if($this->value < 0) {
                $this->sign = '-';
            }
            return;
        }

        throw new Microtime_Exception(
            'Unknown time zone offset name.',
            sprintf(
                'No time zone offset found for name: [%s].',
                $name
            ),
            self::ERROR_UNKNOWN_TIMEZONE_NAME
        );
    }

    /**
     * Returns a lookup table of time zone identifiers.
     * Each time zone has its respective time offset value.
     *
     * @return array<string,array{identifier:string,offset:int}> The keys are lowercase identifiers.
     * @throws Microtime_Exception {@see self::ERROR_DATE_TIME_EXCEPTION}
     */
    public static function getIdentifierOffsets() : array
    {
        // Build the lookup table of all time zone identifiers
        // with their offset values.
        if(isset(self::$identifierOffsets)) {
            return self::$identifierOffsets;
        }

        self::$identifierOffsets = array();

        $identifiers = DateTimeZone::listIdentifiers();

        try
        {
            foreach ($identifiers as $identifier) {
                self::$identifierOffsets[strtolower($identifier)] = array(
                    'identifier' => $identifier,
                    'offset' => (new DateTime('now', new DateTimeZone($identifier)))->getOffset()
                );
            }

            return self::$identifierOffsets;
        }
        catch (Throwable $e)
        {
            throw new Microtime_Exception(
                'Failed to build time zone offset lookup table.',
                sprintf(
                    'Failed to build the lookup table of time zone offsets: %s',
                    $e->getMessage()
                ),
                self::ERROR_DATE_TIME_EXCEPTION,
                $e
            );
        }
    }

    public function isNamed(): bool
    {
        return $this->isNamed;
    }
}
