<?php
/**
 * File containing the class {@see ConvertHelper_URLFinder_Detector_HTMLAttributes}.
 *
 * @package AppUtils
 * @subpackage URLInfo
 * @see ConvertHelper_URLFinder_Detector_HTMLAttributes
 */

declare(strict_types=1);

namespace AppUtils;

/**
 *
 * @package AppUtils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_URLFinder_Detector_HTMLAttributes extends ConvertHelper_URLFinder_Detector
{
    public const REGEX = '/(href|src)="(.*)"/siU';

    public function getRunPosition() : string
    {
        return self::RUN_AFTER;
    }

    public function isValidFor(string $subject) : bool
    {
        return ConvertHelper::isStringHTML($subject);
    }

    protected function filterSubject(string $subject) : string
    {
        return $subject;
    }

    protected function detect(string $subject) : array
    {
        $matches = array();
        preg_match_all(self::REGEX, $subject, $matches, PREG_PATTERN_ORDER);

        $result = array();
        $matches = array_unique($matches[2]);
        foreach($matches as $match) {
            $match = trim($match);
            if(!empty($match) && !in_array($match, $result)) {
                $result[] = $match;
            }
        }

        return $result;
    }
}
