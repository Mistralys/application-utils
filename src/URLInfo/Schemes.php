<?php

declare(strict_types=1);

namespace AppUtils;

/**
 * Class URLInfo_Schemes
 * @package AppUtils
 *
 * @link https://en.wikipedia.org/wiki/List_of_URI_schemes
 */
class URLInfo_Schemes
{
    /**
     * @var string[]
     */
    protected static $schemes = array(
        'http://',
        'https://',
        'mailto:',
        'tel:',
        'git://'


        /*
        'ftp://',
        'sftp://',
        'smb://',
        'sms:',
        'udp://',
        'vnc://',
        'xmpp:',
        'svn:',
        'svn+ssh:',
        'ssh://',
        'bitcoin:',
        'callto:',
        'chrome://',
        'dns://',
        'dns:',
        'ed2k://',
        'facetime://',
        'feed://',
        'feed:',
        'file://',
        'geo:',
        'ldap://',
        'ldaps://',
        'magnet:',
        'im:',
        'steam://',
        'steam:', // command line URI
        'telnet://',
        'teamspeak://'
        */
    );

    /**
     * @var array<string,int>
     */
    protected static $cache = array();

    /**
     * Tries to detect a valid scheme in the specified URL,
     * using the internal list of known schemes.
     *
     * @param string $url
     * @return string|null
     */
    public static function detectScheme(string $url) : ?string
    {
        self::buildCache();

        foreach(self::$cache as $scheme => $length) {
            if(strtolower(substr($url, 0, $length)) === $scheme) {
                return $scheme;
            }
        }

        return null;
    }

    /**
     * Stores the length of each scheme to avoid
     * doing this each time we want to detect a
     * scheme.
     */
    private static function buildCache() : void
    {
        if(!empty(self::$cache)) {
            return;
        }

        foreach(self::$schemes as $scheme) {
            self::$cache[$scheme] = strlen($scheme);
        }
    }
}
