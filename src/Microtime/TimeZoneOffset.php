<?php

declare(strict_types=1);

namespace AppUtils\Microtime;

use AppUtils\Interface_Stringable;
use AppUtils\Microtime_Exception;

class TimeZoneOffset implements Interface_Stringable
{
    public const ERROR_UNRECOGNIZED_TIMEZONE_OFFSET = 144701;
    public const ERROR_UNKNOWN_TIMEZONE_OFFSET_VALUE = 144702;

    private string $name = 'UTC';
    private int $offset = 0;
    private string $sign = '+';

    public function __construct(string $offset)
    {
        $this->parseOffset(trim($offset));
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getValue() : int
    {
        if($this->isNegative()) {
            return $this->getSeconds() * -1;
        }

        return $this->getSeconds();
    }

    public function getSeconds() : int
    {
        return $this->offset;
    }

    public function isPositive() : bool
    {
        return $this->sign === '+';
    }

    public function isNegative() : bool
    {
        return $this->sign === '-';
    }

    public function getHours() : int
    {
        return (int)($this->offset / 60 / 60);
    }

    public function getMinutes() : int
    {
        return (int)($this->offset / 60) % 60;
    }

    public function getAsString() : string
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

    private function parseOffset(string $offset) : void
    {
        if(empty($offset) || $offset === 'Z')
        {
            return;
        }

        if($offset[0] === '+' || $offset[0] === '-')
        {
            $this->sign = $offset[0];
            $this->parseOffsetValue($offset);
            return;
        }

        throw new Microtime_Exception(
            'Unrecognized time zone offset in date.',
            sprintf(
                'No time zone name found for offset: [%s].',
                $offset
            ),
            self::ERROR_UNRECOGNIZED_TIMEZONE_OFFSET
        );
    }

    private function parseOffsetValue(string $offset) : void
    {
        $parts = explode(':', ltrim($offset, '+-'));

        $hour = (int)$parts[0];
        $minute = 0;

        if(isset($parts[1])) {
            $minute = (int)$parts[1];
        }

        $this->offset = ($hour * 60 * 60) + ($minute * 60);

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
                $this->offset
            ),
            self::ERROR_UNKNOWN_TIMEZONE_OFFSET_VALUE
        );
    }

    public function getSign() : string
    {
        return $this->sign;
    }
}
