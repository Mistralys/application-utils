<?php

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser\URLValidator;

use AppUtils\URLInfo;
use AppUtils\URLInfo\Parser\BaseURLValidator;
use function AppUtils\t;

class ValidateHostIsPresent extends BaseURLValidator
{
    public function validate() : bool
    {
        // every link needs a host. This case can happen for ex, if
        // the link starts with a typo with only one slash, like:
        // "http:/hostname"
        if($this->hasHost() || $this->isSchemeLess()) {
            return true;
        }

        $this->parser->setError(
            URLInfo::ERROR_MISSING_HOST,
            t('Cannot determine the link\'s host name.') . ' ' .
            t('This usually happens when there\'s a typo somewhere.')
        );

        return false;
    }
}
