<?php
/**
 * @package AppUtils
 * @subpackage URLInfo
 * @see \AppUtils\URLInfo\URISchemes
 */

declare(strict_types=1);

namespace AppUtils\URLInfo;

/**
 * Class URLInfo_Schemes
 *
 * @package AppUtils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://en.wikipedia.org/wiki/List_of_URI_schemes
 */
class URISchemes
{
    public const ERROR_INVALID_SCHEME = 113601;

    /**
     * @var string[]
     */
    private static array $schemes = array(
        'bitcoin:',
        'callto:',
        'chrome://',
        'data:',
        'dns:',
        'dns://',
        'ed2k://',
        'facetime://',
        'feed:',
        'feed://',
        'file://',
        'file://',
        'ftp://',
        'geo:',
        'git://',
        'http://',
        'https://',
        'im:',
        'ldap://',
        'ldaps://',
        'magnet:',
        'mailto:',
        'sftp://',
        'smb://',
        'sms:',
        'ssh://',
        'steam://',
        'steam:', // command line URI
        'svn+ssh:',
        'svn:',
        'teamspeak://',
        'tel:',
        'telnet://',
        'udp://',
        'vnc://',
        'xmpp:',
    );

    /**
     * Tries to detect a valid scheme in the specified URL,
     * using the internal list of known schemes.
     *
     * @param string $url
     * @return string|null
     */
    public static function detectScheme(string $url) : ?string
    {
        foreach(self::$schemes as $scheme) {
            if(stripos($url, $scheme) === 0) {
                return $scheme;
            }
        }

        return null;
    }

    /**
     * @var string[]|null
     */
    private static ?array $schemeNames = null;

    /**
     * @return string[]
     */
    public static function getSchemeNames() : array
    {
        if(isset(self::$schemeNames)) {
            return self::$schemeNames;
        }

        self::$schemeNames = array();

        foreach(self::$schemes as $scheme) {
            self::$schemeNames[] = self::resolveSchemeName($scheme);
        }

        return self::$schemeNames;
    }

    public static function resolveSchemeName(string $scheme) : string
    {
        $parts = explode(':', $scheme);
        return array_shift($parts);
    }

    /**
     * @return string[]
     */
    public static function getSchemes() : array
    {
        return self::$schemes;
    }

    /**
     * Adds a scheme to the list of known schemes, so it can be
     * detected correctly.
     *
     * @param string $scheme
     * @return void
     * @throws URLException
     */
    public static function addScheme(string $scheme) : void
    {
        self::requireValidScheme($scheme);

        if(!in_array($scheme, self::$schemes, true))
        {
            self::$schemes[] = $scheme;
        }

        self::resetCache();
    }

    public static function requireValidScheme(string $scheme) : void
    {
        if(strpos($scheme, ':'))
        {
            return;
        }

        throw new URLException(
            'Cannot add scheme, invalid format.',
            sprintf(
                'The scheme [%s] is missing the colon separator character. Example: https://.',
                $scheme
            ),
            self::ERROR_INVALID_SCHEME
        );
    }

    public static function isValidScheme(string $scheme) : bool
    {
        return in_array(strtolower($scheme), self::$schemes, true);
    }

    public static function isValidSchemeName(string $scheme) : bool
    {
        return in_array(strtolower($scheme), self::getSchemeNames(), true);
    }

    public static function removeScheme(string $targetScheme) : void
    {
        self::$schemes = array_filter(
            self::$schemes,
            static fn($scheme) => $scheme !== $targetScheme
        );

        self::resetCache();
    }

    /**
     * Resets the cached scheme names. Used after the schemes
     * list has been modified.
     *
     * @return void
     */
    private static function resetCache() : void
    {
        self::$schemeNames = null;
    }
}
