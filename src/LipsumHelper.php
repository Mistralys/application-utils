<?php
/**
 * @package Application Utils
 * @subpackage LipsumHelper
 * @see \AppUtils\LipsumHelper
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\LipsumHelper\LipsumDetector;

/**
 * Lipsum helper with tools all around the often used
 * dummy text. Includes a detector that can find out
 * if a string contains dummy text.
 *
 * @package Application Utils
 * @subpackage LipsumHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see https://lipsum.com
 */
class LipsumHelper
{
    public static function containsLipsum(string $subject) : LipsumDetector
    {
        return new LipsumDetector($subject);
    }
}
