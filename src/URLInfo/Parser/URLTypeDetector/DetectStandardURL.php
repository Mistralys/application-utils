<?php

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser\URLTypeDetector;

use AppUtils\URLInfo\Parser\BaseURLTypeDetector;

class DetectStandardURL extends BaseURLTypeDetector
{
    public function detect() : bool
    {
        if($this->hasHost() || $this->hasQuery() || $this->hasScheme()) {
            $this->setTypeURL();
            return true;
        }

        return false;
    }
}
