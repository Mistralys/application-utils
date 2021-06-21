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
        preg_match_all(RegexHelper::REGEX_IPV4, $subject, $matches, PREG_PATTERN_ORDER);

        return array_unique($matches[0]);
    }
}
