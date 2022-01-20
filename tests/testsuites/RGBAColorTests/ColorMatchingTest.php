<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;

class ColorMatchingTest extends TestCase
{
    public function test_matchColorOnly() : void
    {
        $colorA = ColorFactory::createFromHEX('CCC');
        $colorB = ColorFactory::createFromHEX('CCC');

        $this->assertTrue($colorA->matches($colorB));
    }

    public function test_matchColorAndAlpha() : void
    {
        $colorA = ColorFactory::createFromHEX('CCCCCCCC');
        $colorB = ColorFactory::createFromHEX('CCCCCCDD');

        $this->assertTrue($colorA->matches($colorB));
        $this->assertFalse($colorA->matchesAlpha($colorB));
    }
}
