<?php

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser\URLTypeDetector;

use AppUtils\URLInfo;
use AppUtils\URLInfo\Parser\BaseURLTypeDetector;

class DetectPhoneLink extends BaseURLTypeDetector
{
    public function detect() : bool
    {
        if($this->getScheme() === 'tel') {
            $this->setTypePhone();
            return true;
        }

        return false;
    }

    private function setTypePhone() : void
    {
        $this->setType(URLInfo::TYPE_PHONE);
    }
}
