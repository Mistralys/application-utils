<?php
/**
 * @package Application Utils
 * @subpackage ClassFinder
 * @see \AppUtils\ClassHelper\ClassLoaderNotFoundException
 */

declare(strict_types=1);

namespace AppUtils\ClassHelper;

/**
 * @package Application Utils
 * @subpackage ClassFinder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ClassLoaderNotFoundException extends BaseClassHelperException
{
    public const ERROR_CODE = 111101;

    /**
     * @param string[] $searchPaths
     */
    public function __construct(array $searchPaths)
    {
        parent::__construct(
            'Composer auto-loader not found.',
            sprintf(
                'The composer auto-loader could not be found in any of the possible paths:'.PHP_EOL.
                '%s',
                '- '.implode(PHP_EOL.'- ', $searchPaths)
            ),
            self::ERROR_CODE
        );
    }
}
