<?php
/**
 * @package Application Utils Tests
 * @subpackage URLBuilder
 */

declare(strict_types=1);

namespace AppUtilsTestClasses\Stubs;

use AppUtils\URLBuilder\URLBuilder;

/**
 * @package Application Utils Tests
 * @subpackage URLBuilder
 */
class StubCustomURLBuilder extends URLBuilder implements StubCustomURLBuilderInterface
{
    /**
     * @inheritDoc
     * @return StubCustomURLBuilder
     */
    public static function create(array $params=array()) : StubCustomURLBuilder
    {
        return new self($params);
    }

    public function customParam(string $value) : self
    {
        return $this->string('custom', $value);
    }
}
