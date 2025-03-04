<?php
/**
 * @package Application Utils Tests
 * @subpackage URLBuilder
 */

declare(strict_types=1);

namespace AppUtilsTestClasses\Stubs;

use AppUtils\URLBuilder\URLBuilderInterface;

/**
 * @package Application Utils Tests
 * @subpackage URLBuilder
 */
interface StubCustomURLBuilderInterface extends URLBuilderInterface
{
    public function customParam(string $value) : self;
}
