<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\RGBAColor\ColorPresets;
use AppUtils\RGBAColor\FormatsConverter;
use AppUtils\RGBAColor\UnitsConverter;
use PHPUnit\Framework\TestCase;

final class CoreTest extends TestCase
{
    public const RED_PERCENT = 10.45;
    public const GREEN_PERCENT = 45.78;
    public const BLUE_PERCENT = 99.10;
    public const RED_INTEGER = 27;
    public const GREEN_INTEGER = 117;
    public const BLUE_INTEGER = 253;
    public const ALPHA_INTEGER = 84;
    public const ALPHA_PERCENT = 33;

    public function test_percent2int() : void
    {
        $tests = array(
            array(
                'percent' => 50,
                'expected' => 128 // 127.5 rounded up
            ),
            array(
                'percent' => 22.45,
                'expected' => 57 // 57.2475 rounded down
            )
        );

        foreach ($tests as $test)
        {
            $result = UnitsConverter::percent2IntEightBit($test['percent']);

            $this->assertSame($result, $test['expected']);
        }
    }

    public function test_createInstanceWithoutAlpha() : void
    {
        $color = ColorFactory::createPercent(
            self::RED_PERCENT,
            self::GREEN_PERCENT,
            self::BLUE_PERCENT
        );

        // 10.45 -> 26.6475 -> 27 rounded up
        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());

        // 45.78 -> 116.739 -> 117 rounded up
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());

        // 99.10 -> 252.705 -> 253 rounded up
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());

        $this->assertSame(255, $color->getOpacity()->get8Bit());
    }

    public function test_createInstanceWithAlpha() : void
    {
        $color = ColorFactory::createPercent(
            self::RED_PERCENT,
            self::GREEN_PERCENT,
            self::BLUE_PERCENT,
            self::ALPHA_PERCENT
        );

        // 84.15 -> 84 rounded down
        $this->assertSame(self::ALPHA_INTEGER, $color->getOpacity()->get8Bit());
    }

    public function test_createInstanceGetPercent() : void
    {
        $color = ColorFactory::createPercent(
            self::RED_PERCENT,
            self::GREEN_PERCENT,
            self::BLUE_PERCENT,
            self::ALPHA_PERCENT
        );

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
        $this->assertSame(self::ALPHA_INTEGER, $color->getOpacity()->get8Bit());
    }

    public function test_transparency() : void
    {
        $transparent = ColorPresets::white()
            ->setTransparency(ColorChannel::percent(100));

        $this->assertEquals(100, $transparent->getTransparency()->getPercent());
        $this->assertEquals(0, $transparent->getOpacity()->getPercent());

        $opaque = ColorPresets::white()
            ->setTransparency(ColorChannel::percent(0));

        $this->assertEquals(0, $opaque->getTransparency()->getPercent());
        $this->assertEquals(100, $opaque->getOpacity()->getPercent());
    }

    public function test_opacity() : void
    {
        $transparent = ColorPresets::white()
            ->setOpacity(ColorChannel::percent(0));

        $this->assertEquals(100, $transparent->getTransparency()->getPercent());
        $this->assertEquals(0, $transparent->getOpacity()->getPercent());

        $opaque = ColorPresets::white()
            ->setOpacity(ColorChannel::percent(100));

        $this->assertEquals(0, $opaque->getTransparency()->getPercent());
        $this->assertEquals(100, $opaque->getOpacity()->getPercent());
    }

    public function test_isValidColorArray() : void
    {
        $this->assertFalse(FormatsConverter::isColorArray(array()));

        $this->assertFalse(FormatsConverter::isColorArray(array(
            RGBAColor::CHANNEL_RED => self::RED_INTEGER,
            RGBAColor::CHANNEL_GREEN => self::GREEN_INTEGER
        )));

        $this->assertTrue(FormatsConverter::isColorArray(array(
            RGBAColor::CHANNEL_RED => self::RED_INTEGER,
            RGBAColor::CHANNEL_GREEN => self::GREEN_INTEGER,
            RGBAColor::CHANNEL_BLUE => self::BLUE_INTEGER
        )));

        $this->assertTrue(FormatsConverter::isColorArray(array(
            RGBAColor::CHANNEL_RED => self::RED_INTEGER,
            RGBAColor::CHANNEL_GREEN => self::GREEN_INTEGER,
            RGBAColor::CHANNEL_BLUE => self::BLUE_INTEGER,
            RGBAColor::CHANNEL_ALPHA => self::ALPHA_INTEGER
        )));
    }

    public function test_requireValidColorArray() : void
    {
        $this->expectExceptionCode(RGBAColor::ERROR_INVALID_COLOR_ARRAY);

        FormatsConverter::requireValidColorArray(array());
    }
}
