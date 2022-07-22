<?php

declare(strict_types=1);

namespace AppUtils\URLInfo;

class URLHosts
{
    private static array $knownHosts = array(
        'localhost'
    );

    public static function isHostKnown(string $host) : bool
    {
        return in_array(strtolower($host), self::$knownHosts, true);
    }

    public static function addHost(string $host) : void
    {
        if(!self::isHostKnown($host)) {
            self::$knownHosts[] = strtolower($host);
        }
    }
}
