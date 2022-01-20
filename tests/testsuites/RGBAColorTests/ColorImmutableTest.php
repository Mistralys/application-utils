<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;

class ColorImmutableTest extends TestCase
{
    public function test_setRed() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200);

        $modified = $color->setRed(ColorChannel::EightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getRed()->get8Bit());
        $this->assertSame(100, $modified->getRed()->get8Bit());
    }

    public function test_setGreen() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200);

        $modified = $color->setGreen(ColorChannel::EightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getGreen()->get8Bit());
        $this->assertSame(100, $modified->getGreen()->get8Bit());
    }

    public function test_setBlue() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200);

        $modified = $color->setBlue(ColorChannel::EightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getBlue()->get8Bit());
        $this->assertSame(100, $modified->getBlue()->get8Bit());
    }

    public function test_setOpacity() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200, 200);

        $modified = $color->setOpacity(ColorChannel::EightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getOpacity()->get8Bit());
        $this->assertSame(100, $modified->getOpacity()->get8Bit());
    }

    public function test_setTransparency() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200, 200);

        $modified = $color->setTransparency(ColorChannel::EightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(55, $color->getTransparency()->get8Bit());
        $this->assertSame(100, $modified->getTransparency()->get8Bit());
    }

    public function test_setColor() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200, 200);

        $modified = $color->setColor(RGBAColor::CHANNEL_GREEN, ColorChannel::EightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getGreen()->get8Bit());
        $this->assertSame(100, $modified->getGreen()->get8Bit());
    }
}
