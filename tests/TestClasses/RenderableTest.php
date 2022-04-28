<?php
/**
 * @package Application Utils
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace TestClasses;

use AppUtils\Interfaces\RenderableInterface;
use AppUtils\Traits\RenderableTrait;

/**
 * @package Application Utils
 * @subpackage UnitTests
 */
class RenderableTest implements RenderableInterface
{
    use RenderableTrait;

    public const RENDERED_TEXT = 'Hello world';

    public function render() : string
    {
        return self::RENDERED_TEXT;
    }
}
