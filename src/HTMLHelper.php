<?php

declare(strict_types=1);

namespace AppUtils;

class HTMLHelper
{
    public static function stripComments(string $html) : string
    {
        return preg_replace('/<!--(?!<!)[^\[>].*?-->/si', '', $html);
    }
}
