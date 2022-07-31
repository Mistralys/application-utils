<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;
use testsuites\FileHelperTests\PathInfoTest;

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

    public function test_matchMixedColors() : void
    {
        $colorA = ColorFactory::createFromHEX('FFFFFF00');
        $colorB = ColorFactory::createFromHEX('FFFFFF');

        $this->assertTrue($colorA->matches($colorB));
        $this->assertTrue($colorA->matchesAlpha($colorB));
    }

    public function test_getOpacity() : void
    {
        $colorA = ColorFactory::createFromHEX('FFFFFF00');
        $colorB = ColorFactory::createFromHEX('FFFFFF');

        $this->assertSame($colorA->getAlpha()->get8Bit(), 0);
        $this->assertSame($colorB->getAlpha()->get8Bit(), 0);
    }
}
