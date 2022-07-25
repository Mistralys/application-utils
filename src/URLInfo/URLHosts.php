<?php
/**
 * @package Application Utils
 * @subpackage URLInfo
 * @see \AppUtils\URLInfo\URLHosts
 */

declare(strict_types=1);

namespace AppUtils\URLInfo;

use AppUtils\URLInfo;

/**
 * Host name collection used by the {@see URLInfo} class
 * for detecting host names when using host only URLs,
 * e.g. <code>parseURL('hostname')</pre>.
 *
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLHosts
{
    /**
     * @var string[]
     */
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
