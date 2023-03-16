<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\RGBAColor\UnitsConverter;
use PHPUnit\Framework\TestCase;

class ColorConverterTest extends TestCase
{
    public function test_array8Bit() : void
    {
        $array = ColorFactory::createFromHEX('CCC')
            ->toArray()
            ->eightBit();

        $this->assertSame(204, $array[RGBAColor::CHANNEL_RED]);
    }

    public function test_arrayPercent() : void
    {
        $array = ColorFactory::createFromHEX('CCC')
            ->toArray()
            ->percent();

        $this->assertSame(80.0, $array[RGBAColor::CHANNEL_RED]);
    }

    public function test_arrayGD() : void
    {
        $array = ColorFactory::createFromHEX('CCC')
            ->toArray()
            ->GD();

        $this->assertSame(204, $array[RGBAColor::CHANNEL_RED]);
        $this->assertSame(0, $array[RGBAColor::CHANNEL_ALPHA]);
    }

    public function test_arrayCSS() : void
    {
        $array = ColorFactory::createFromHEX('CCCCCCCC')
            ->toArray()
            ->CSS();

        $this->assertSame(204, $array[RGBAColor::CHANNEL_RED]);
        $this->assertSame(0.8, $array[RGBAColor::CHANNEL_ALPHA]);
    }

    public function test_toCSS() : void
    {
        $this->assertSame(
            'rgb(204, 204, 204)',
            ColorFactory::createFromHEX('CCC')->toCSS()
        );

        $this->assertSame(
            'rgba(204, 204, 204, 0.8)',
            ColorFactory::createFromHEX('CCCCCCCC')->toCSS()
        );
    }

    public function test_toHEX() : void
    {
        $this->assertSame('CCCCCC', ColorFactory::createFromHEX('CCC')->toHEX());
        $this->assertSame('CCCCCCCC', ColorFactory::createFromHEX('CCCCCCCC')->toHEX());
    }

    public function test_int2hex() : void
    {
        $this->assertSame('0A', UnitsConverter::int2hex(10));
    }

    public function test_alpha2Int8Bit() : void
    {
        $this->assertSame(255, UnitsConverter::alpha2IntEightBit(1));
        $this->assertSame(0, UnitsConverter::alpha2IntEightBit(0));
        $this->assertSame(128, UnitsConverter::alpha2IntEightBit(0.5));
    }

    public function test_alpha2Int7Bit() : void
    {
        $this->assertSame(127, UnitsConverter::alpha2IntSevenBit(1));
        $this->assertSame(0, UnitsConverter::alpha2IntSevenBit(0));
        $this->assertSame(64, UnitsConverter::alpha2IntSevenBit(0.5));
    }
}
