<?php
/**
 * File containing the class {@see ConvertHelper_URLFinder_Detector_Tel}.
 *
 * @package AppUtils
 * @subpackage URLInfo
 * @see ConvertHelper_URLFinder_Detector_Tel
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Class used to detect `tel:` phone number links in a string.
 * Allows for the full RFC syntax, and automatically corrects
 * links that mistakenly use `tel://` notation.
 *
 * Matching examples:
 *
 * tel:+1(800)555-1212
 * tel:+18005551212,+18005553434;ext=123
 * tel:911;phone-context=+1
 * tel:123;phone-context=example.com
 *
 * @package AppUtils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://www.ietf.org/rfc/rfc3966.txt
 * @link https://snipplr.com/view/11540/regex-for-tel-uris
 */
class ConvertHelper_URLFinder_Detector_Tel extends ConvertHelper_URLFinder_Detector
{
    const REGEX = '/^tel:((?:\+[\d().-]*\d[\d().-]*|[0-9A-F*#().-]*[0-9A-F*#][0-9A-F*#().-]*(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*;phone-context=(?:\+[\d().-]*\d[\d().-]*|(?:[a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*(?:[a-z]|[a-z][a-z0-9-]*[a-z0-9])))(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*(?:,(?:\+[\d().-]*\d[\d().-]*|[0-9A-F*#().-]*[0-9A-F*#][0-9A-F*#().-]*(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*;phone-context=\+[\d().-]*\d[\d().-]*)(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*)*)$/s';

    public function getRunPosition() : string
    {
        return self::RUN_BEFORE;
    }

    public function isValidFor(string $subject) : bool
    {
        return stristr($subject, 'tel:') !== false;
    }

    protected function filterSubject(string $subject) : string
    {
        return str_replace('tel://', 'tel:', $subject);
    }

    protected function detect(string $subject) : array
    {
        $matches = array();
        preg_match_all(self::REGEX, $subject, $matches, PREG_PATTERN_ORDER);

        return array_unique($matches[0]);
    }
}
