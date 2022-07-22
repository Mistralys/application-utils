<?php

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser\URLTypeDetector;

use AppUtils\RegexHelper;
use AppUtils\URLInfo\Parser\BaseURLTypeDetector;

class DetectIPAddress extends BaseURLTypeDetector
{
    public function detect() : bool
    {
        if(!$this->hasHost() && $this->hasPath() && preg_match(RegexHelper::REGEX_IPV4, $this->getPath())) {
            $this
                ->setHost($this->getPath())
                ->setSchemeHTTPS()
                ->setIP($this->getHost())
                ->setTypeURL()
                ->removePath();

            return true;
        }

        if($this->hasHost() && preg_match(RegexHelper::REGEX_IPV4, $this->getHost())) {
            $this->setIP($this->getHost());
            $this->setTypeURL();
            return true;
        }

        return false;
    }
}
