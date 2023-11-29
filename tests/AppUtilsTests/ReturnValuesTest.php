<?php

declare(strict_types=1);

namespace AppUtilsTests;

use PHPUnit\Framework\TestCase;
use function AppUtils\valBool;
use function AppUtils\valBoolTrue;
use function AppUtils\valBoolFalse;

final class ReturnValuesTest extends TestCase
{
    public function test_boolDefault() : void
    {
        $bool = valBool();
        $this->assertFalse($bool->get());

        $bool = valBool(true);
        $this->assertTrue($bool->get());
    }

    public function test_boolSet() : void
    {
        $bool = valBool();
        $this->assertFalse($bool->get());

        $bool->set(true);
        $this->assertTrue($bool->get());

        $bool->set(false);
        $this->assertFalse($bool->get());
    }

    public function test_boolTrueDefault() : void
    {
        $bool = valBoolTrue();
        $this->assertFalse($bool->get());

        $bool = valBoolTrue(true);
        $this->assertTrue($bool->get());
    }

    public function test_boolTrueSet() : void
    {
        $bool = valBoolTrue();
        $this->assertFalse($bool->get());

        $bool->set(false);
        $this->assertFalse($bool->get());

        $bool->set(true);
        $this->assertTrue($bool->get());

        $bool->set(false);
        $this->assertTrue($bool->get());
    }

    public function test_boolFalseDefault() : void
    {
        $bool = valBoolFalse();
        $this->assertTrue($bool->get());

        $bool = valBoolFalse(false);
        $this->assertFalse($bool->get());
    }

    public function test_boolFalseSet() : void
    {
        $bool = valBoolFalse();
        $this->assertTrue($bool->get());

        $bool->set(true);
        $this->assertTrue($bool->get());

        $bool->set(false);
        $this->assertFalse($bool->get());

        $bool->set(true);
        $this->assertFalse($bool->get());
    }
}
