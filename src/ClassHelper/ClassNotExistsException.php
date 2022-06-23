<?php
/**
 * @package Application Utils
 * @subpackage ClassFinder
 * @see \AppUtils\ClassHelper\ClassNotExistsException
 */

declare(strict_types=1);

namespace AppUtils\ClassHelper;

use Throwable;

/**
 * @package Application Utils
 * @subpackage ClassFinder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ClassNotExistsException extends BaseClassHelperException
{
    public const ERROR_CODE = 110901;

    public function __construct(string $class, ?int $code = null, ?Throwable $previous = null)
    {
        if($code === null || $code === 0) {
            $code = self::ERROR_CODE;
        }

        parent::__construct(
            'Class does not exist.',
            sprintf(
                'The class [%s] cannot be auto-loaded.',
                $class
            ),
            $code,
            $previous
        );
    }
}
