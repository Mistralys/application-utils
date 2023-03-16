<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites\HSVColorTests;

use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;

final class OperationsTest extends TestCase
{
    public function test_redBasedColor() : void
    {
        $rgb = ColorFactory::create8Bit(120, 40, 40);

        $hsv = $rgb->toHSV();

        // Hue is 0, because red is where the hue spectrum starts.
        $this->assertSame(0, $hsv->getHue()->getValueRounded());
        $this->assertSame(47, $hsv->getBrightness()->getPercentRounded());
        $this->assertSame(67, $hsv->getSaturation()->getPercentRounded());
    }

    public function test_blueBasedColor() : void
    {
        $rgb = ColorFactory::create8Bit(40, 40, 200);

        $hsv = $rgb->toHSV();

        $this->assertSame(240.0, round($hsv->getHue()->getValue()));
        $this->assertSame(78.0, round($hsv->getBrightness()->getValue()));
        $this->assertSame(80.0, round($hsv->getSaturation()->getValue()));
    }

    public function test_increaseBrightness() : void
    {
        $hsv = ColorFactory::create8Bit(37, 128, 37)->toHSV();

        // The brightness should be at 50% to start with
        $this->assertSame(50.0, round($hsv->getBrightness()->getValue()));

        $adjusted = $hsv->adjustBrightness(20);

        $this->assertSame(60.0, round($adjusted->getBrightness()->getValue()));
    }

    public function test_decreaseBrightness() : void
    {
        $hsv = ColorFactory::create8Bit(37, 128, 37)->toHSV();

        // The brightness should be at 50% to start with
        $this->assertSame(50.0, round($hsv->getBrightness()->getValue()));

        $adjusted = $hsv->adjustBrightness(-20);

        $this->assertSame(40.0, round($adjusted->getBrightness()->getValue()));
    }

    public function test_brightnessViaRGBColor() : void
    {
        $rgb = ColorFactory::create8Bit(40, 140, 40)
            ->setBrightness(20);

        $this->assertSame(15, $rgb->getRed()->get8Bit());
        $this->assertSame(51, $rgb->getGreen()->get8Bit());
        $this->assertSame(15, $rgb->getBlue()->get8Bit());
        $this->assertSame(20.0, $rgb->getBrightness()->getValue());
    }

    public function test_setHue() : void
    {
        $hsv = ColorFactory::createHSV(122, 44, 78);

        $modified = $hsv->setHue(114.87);

        $this->assertSame(114.87, $modified->getHue()->getValue());
        $this->assertSame(122, $hsv->getHue()->getValueRounded());
    }

    public function test_setSaturation() : void
    {
        $hsv = ColorFactory::createHSV(122, 44, 78);

        $modified = $hsv->setSaturation(81.11);

        $this->assertSame(81.11, $modified->getSaturation()->getPercent());
        $this->assertSame(44, $hsv->getSaturation()->getPercentRounded());
    }

    public function test_setAlpha() : void
    {
        $hsv = ColorFactory::createHSV(122, 44, 78, 1);

        $modified = $hsv->setAlpha(0.237);

        $this->assertSame(0.237, $modified->getAlpha()->getAlpha());
        $this->assertSame(1.0, $hsv->getAlpha()->getAlpha());
    }
}
