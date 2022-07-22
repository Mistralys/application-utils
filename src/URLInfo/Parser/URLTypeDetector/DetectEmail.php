<?php
/**
 * @package Application Utils
 * @subpackage URLInfo
 */

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser\URLTypeDetector;

use AppUtils\RegexHelper;
use AppUtils\URLInfo\Parser\BaseURLTypeDetector;

/**
 * Detects whether the URL is an Email address.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DetectEmail extends BaseURLTypeDetector
{
    public function detect() : bool
    {
        if($this->getScheme() === 'mailto') {
            $this->setHostFromEmail($this->getPath());
            $this->setTypeEmail();
            return true;
        }

        if($this->hasPath() && preg_match(RegexHelper::REGEX_EMAIL, $this->getPath()))
        {
            $this->setHostFromEmail($this->getPath());
            $this->setSchemeMailto();
            $this->setTypeEmail();
            return true;
        }

        return false;
    }
}
