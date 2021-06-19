<?php
/**
 * File containing the class {@see ConvertHelper_URLFinder_Detector_IPV4}.
 *
 * @package AppUtils
 * @subpackage URLInfo
 * @see ConvertHelper_URLFinder_Detector_IPV4
 */

declare(strict_types=1);

namespace AppUtils;

/**
 *
 * @package AppUtils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_URLFinder_Detector_IPV4 extends ConvertHelper_URLFinder_Detector
{
    const REGEX = '/((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/siU';

    public function getRunPosition() : string
    {
        return self::RUN_AFTER;
    }

    public function isValidFor(string $subject) : bool
    {
        return true;
    }

    protected function filterSubject(string $subject) : string
    {
        return $subject;
    }

    protected function detect(string $subject) : array
    {
        $matches = array();
        preg_match_all(self::REGEX, $subject, $matches, PREG_PATTERN_ORDER);

        return array_unique($matches[0]);
    }
}
