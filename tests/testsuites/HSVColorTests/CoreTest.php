<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites\HSVColorTests;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;

final class CoreTest extends TestCase
{
    /**
     * Ensure that when converting to HSV and back,
     * the alpha channel is preserved.
     */
    public function test_alphaPassThrough() : void
    {
        $rgb = ColorFactory::create(
            ColorChannel::eightBit(145),
            ColorChannel::eightBit(78),
            ColorChannel::eightBit(39),
            ColorChannel::alpha(0.6)
        );

        $hsv = $rgb->toHSV();

        $this->assertSame(0.6, $hsv->getAlpha()->getValue());

        $rgb = $hsv->toRGB();

        $this->assertSame(0.6, $rgb->getAlpha()->getAlpha());
    }
}
