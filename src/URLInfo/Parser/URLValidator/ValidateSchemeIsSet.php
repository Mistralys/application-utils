<?php

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser\URLValidator;

use AppUtils\URLInfo;
use AppUtils\URLInfo\Parser\BaseURLValidator;
use function AppUtils\t;

class ValidateSchemeIsSet extends BaseURLValidator
{
    public function validate() : bool
    {
        if($this->hasScheme() || $this->isFragmentOnly()) {
            return true;
        }

        // no scheme found: it may be an email address without the mailto:
        // It can't be a variable, since without the scheme it would already
        // have been recognized as a variable only link.
        $this->parser->setError(
            URLInfo::ERROR_MISSING_SCHEME,
            t('Cannot determine the link\'s scheme, e.g. %1$s.', 'http')
        );

        return false;
    }
}
