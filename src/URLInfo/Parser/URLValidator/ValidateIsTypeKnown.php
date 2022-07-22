<?php

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser\URLValidator;

use AppUtils\URLInfo;
use AppUtils\URLInfo\Parser\BaseURLValidator;
use function AppUtils\t;

class ValidateIsTypeKnown extends BaseURLValidator
{
    public function validate() : bool
    {
        if($this->getType() !== URLInfo::TYPE_NONE)
        {
            return true;
        }

        $this->parser->setError(
            URLInfo::ERROR_UNKNOWN_TYPE,
            t('Could not detect the URL type.')
        );

        return false;
    }
}
