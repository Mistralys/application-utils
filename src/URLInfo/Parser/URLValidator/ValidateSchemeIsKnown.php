<?php

declare(strict_types=1);

namespace AppUtils\URLInfo\Parser\URLValidator;

use AppUtils\URLInfo;
use AppUtils\URLInfo\Parser\BaseURLValidator;
use AppUtils\URLInfo\URISchemes;
use function AppUtils\t;

class ValidateSchemeIsKnown extends BaseURLValidator
{
    public function validate() : bool
    {
        if(!$this->hasScheme() || URISchemes::isValidSchemeName((string)$this->getScheme())) {
            return true;
        }

        $this->setScheme('');

        $this->parser->setError(
            URLInfo::ERROR_INVALID_SCHEME,
            t('The scheme %1$s is not supported for links.', $this->getScheme()) . ' ' .
            t('Valid schemes are: %1$s.', implode(', ', URISchemes::getSchemeNames()))
        );

        return false;
    }
}
