<?php

declare(strict_types=1);

namespace AppUtils;

use Stringable;

interface Interface_Stringable extends Stringable
{
    /**
     * Converts the object to a string.
     *
     * @return string
     */
    public function __toString();
}
