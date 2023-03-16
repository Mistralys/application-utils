<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites\HSVColorTests;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;

final class FactoryTest extends TestCase
{
    public function test_createFromArrayAssoc() : void
    {
        $hsv = ColorFactory::createHSVFromArray(array(
            'hue' => 140,
            'saturation' => 33,
            'brightness' => 22
        ));

        $this->assertSame(140, $hsv->getHue()->getValueRounded());
        $this->assertSame(33, $hsv->getSaturation()->getPercentRounded());
        $this->assertSame(22, $hsv->getBrightness()->getPercentRounded());
    }

    public function test_createFromArrayIndex() : void
    {
        $hsv = ColorFactory::createHSVFromArray(array(140, 33, 22));

        $this->assertSame(140, $hsv->getHue()->getValueRounded());
        $this->assertSame(33, $hsv->getSaturation()->getPercentRounded());
        $this->assertSame(22, $hsv->getBrightness()->getPercentRounded());
    }

    public function test_createFromArrayMixed() : void
    {
        $hsv = ColorFactory::createHSVFromArray(array(
            1 => 33,
            2 => ColorChannel::brightness(22),
            'hue' => 140,
        ));

        $this->assertSame(140, $hsv->getHue()->getValueRounded());
        $this->assertSame(33, $hsv->getSaturation()->getPercentRounded());
        $this->assertSame(22, $hsv->getBrightness()->getPercentRounded());
    }

    public function test_createFromInvalidArray() : void
    {
        $this->expectExceptionCode(ColorFactory::ERROR_INVALID_HSV_COLOR_ARRAY);

        ColorFactory::createHSVFromArray(array());
    }
}
