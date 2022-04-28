<?php
/**
 * @package Application Utils
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace TestClasses;

use AppUtils\BaseException;
use AppUtils\Interfaces\RenderableInterface;
use AppUtils\Traits\RenderableTrait;

/**
 * @package Application Utils
 * @subpackage UnitTests
 */
class RenderableExceptionTest implements RenderableInterface
{
    public const EXCEPTION_MESSAGE = 'Rendering failed with an exception.';

    use RenderableTrait;

    public function render() : string
    {
        throw new BaseException(self::EXCEPTION_MESSAGE);
    }
}
