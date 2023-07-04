<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\StyleCollection\StyleBuilder\Flavors\Color;
use PHPUnit\Framework\TestCase;

class ColorImmutableTest extends TestCase
{
    public function test_setRed() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200);

        $modified = $color->setRed(ColorChannel::eightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getRed()->get8Bit());
        $this->assertSame(100, $modified->getRed()->get8Bit());
    }

    public function test_setGreen() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200);

        $modified = $color->setGreen(ColorChannel::eightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getGreen()->get8Bit());
        $this->assertSame(100, $modified->getGreen()->get8Bit());
    }

    public function test_setBlue() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200);

        $modified = $color->setBlue(ColorChannel::eightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getBlue()->get8Bit());
        $this->assertSame(100, $modified->getBlue()->get8Bit());
    }

    public function test_setOpacity() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200, 200);

        $modified = $color->setAlpha(ColorChannel::eightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getAlpha()->get8Bit());
        $this->assertSame(100, $modified->getAlpha()->get8Bit());
    }

    public function test_setTransparency() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200, 200);

        $modified = $color->setTransparency(ColorChannel::eightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(55, $color->getTransparency()->get8Bit());
        $this->assertSame(100, $modified->getTransparency()->get8Bit());
    }

    public function test_setColor() : void
    {
        $color = ColorFactory::create8Bit(200, 200, 200, 200);

        $modified = $color->setColor(RGBAColor::CHANNEL_GREEN, ColorChannel::eightBit(100));

        $this->assertNotSame($color, $modified);
        $this->assertSame(200, $color->getGreen()->get8Bit());
        $this->assertSame(100, $modified->getGreen()->get8Bit());
    }

    public function test_applyNonImmutable() : void
    {
        $color = ColorFactory::preset()->white();

        $applied = $color
            ->applyRed(ColorChannel::eightBit(11))
            ->applyGreen(ColorChannel::eightBit(22))
            ->applyBlue(ColorChannel::eightBit(33))
            ->applyAlpha(ColorChannel::eightBit(44));

        $this->assertSame($color, $applied);
        $this->assertSame($color->getRed()->get8Bit(), 11);
        $this->assertSame($color->getGreen()->get8Bit(), 22);
        $this->assertSame($color->getBlue()->get8Bit(), 33);
        $this->assertSame($color->getAlpha()->get8Bit(), 44);
    }
}
