<?php
/**
 * File containing the class {@see ConvertHelper_URLFinder_DomainExtensions}
 *
 * @package AppUtils
 * @subpackage URLFinder
 * @see ConvertHelper_URLFinder_DomainExtensions
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Access a list of all top level domain extensions.
 *
 * @package AppUtils
 * @subpackage URLFinder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://data.iana.org/TLD/tlds-alpha-by-domain.txt
 */
class ConvertHelper_URLFinder_DomainExtensions
{
    /**
     * @var string
     */
    private static $cacheFile = '';

    /**
     * @var string[]
     */
    private static $names = array();

    public function __construct()
    {
        if(empty(self::$cacheFile)) {
            self::$cacheFile = __DIR__.'/tlds-alpha-by-domain.txt';
        }

        $this->load();
    }

    /**
     * @return string[]
     */
    public function getNames() : array
    {
        return self::$names;
    }

    public function nameExists(string $name) : bool
    {
        $name = strtolower($name);

        return isset(self::$names[$name]);
    }

    private function load() : void
    {
        if(!empty(self::$names)) {
            return;
        }

        $content = strtolower(FileHelper::readContents(self::$cacheFile));

        $names = ConvertHelper::explodeTrim("\n", $content);

        // Store the names as keys in the array to allow
        // faster lookups (to avoid using in_array).
        foreach($names as $name) {
            self::$names[$name] = '';
        }
    }
}
