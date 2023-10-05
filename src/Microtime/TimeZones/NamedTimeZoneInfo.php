<?php
/**
 * @package Application Utils
 * @subpackage Microtime
 * @see \AppUtils\Microtime\TimeZones\NamedTimeZoneInfo
 */

declare(strict_types=1);

namespace AppUtils\Microtime\TimeZones;

use DateTimeZone;

/**
 * Like {@see TimeZoneInfo}, but implements the {@see self::getName()}
 * method, which is immutable. This is unlike the base class'
 * {@see TimeZoneInfo::getAnyName()}, which can't be relied on, as
 * there can be several names in the same time offset.
 *
 * @package Application Utils
 * @subpackage Microtime
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class NamedTimeZoneInfo extends TimeZoneInfo
{
    public function getName() : string
    {
        return $this->getAnyName();
    }

    public function getDateTimeZone() : DateTimeZone
    {
        if(!isset($this->dateTimeZone)) {
            $this->dateTimeZone = new DateTimeZone($this->getName());
        }

        return $this->dateTimeZone;
    }
}
