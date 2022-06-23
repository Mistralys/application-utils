<?php
/**
 * @package Application Utils
 * @subpackage ClassFinder
 * @see \AppUtils\ClassHelper\ClassNotImplementsException
 */

declare(strict_types=1);

namespace AppUtils\ClassHelper;

use function AppUtils\parseVariable;
use Throwable;

/**
 * @package Application Utils
 * @subpackage ClassFinder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ClassNotImplementsException extends BaseClassHelperException
{
    /**
     * @param string $expectedClass
     * @param mixed $given
     * @param int|null $code
     * @param Throwable|null $previous
     */
    public function __construct(string $expectedClass, $given, ?int $code = null, ?Throwable $previous = null)
    {
        parent::__construct(
            'Subject does not extend the expected class.',
            sprintf(
                'Expected an instance of [%s], given: [%s].',
                $expectedClass,
                parseVariable($given)->enableType()->toString()
            ),
            $code,
            $previous
        );
    }
}
