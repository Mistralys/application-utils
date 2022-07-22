<?php

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser\URLTypeDetector;

use AppUtils\URLInfo;
use AppUtils\URLInfo\Parser\BaseURLTypeDetector;

class DetectFragmentLink extends BaseURLTypeDetector
{
    public function detect() : bool
    {
        if($this->hasFragment() && (!$this->hasScheme() && !$this->hasHost() && !$this->hasQuery() && !$this->hasPath())) {
            $this->setTypeFragment();
            return true;
        }

        return false;
    }

    private function setTypeFragment() : void
    {
        $this->setType(URLInfo::TYPE_FRAGMENT);
    }
}
