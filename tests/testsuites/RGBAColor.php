<?php

declare(strict_types=1);

use AppUtils\RGBAColor;
use AppUtils\RGBAColor_Converter;
use AppUtils\RGBAColor_Factory;
use AppUtils\RGBAColor_Presets;
use PHPUnit\Framework\TestCase;

final class RGBAColorTests extends TestCase
{
    const RED_PERCENT = 10.45;
    const GREEN_PERCENT = 45.78;
    const BLUE_PERCENT = 99.10;
    const RED_INTEGER = 27;
    const GREEN_INTEGER = 117;
    const BLUE_INTEGER = 253;
    const ALPHA_INTEGER = 84;
    const ALPHA_PERCENT = 33;

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
            $result = RGBAColor_Converter::percent2int($test['percent']);

            $this->assertSame($result, $test['expected']);
        }
    }

    public function test_createInstanceWithoutAlpha() : void
    {
        $color = new RGBAColor(
            self::RED_PERCENT,
            self::GREEN_PERCENT,
            self::BLUE_PERCENT
        );

        // 10.45 -> 26.6475 -> 27 rounded up
        $this->assertSame(self::RED_INTEGER, $color->getRed());

        // 45.78 -> 116.739 -> 117 rounded up
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());

        // 99.10 -> 252.705 -> 253 rounded up
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());

        $this->assertSame(255, $color->getAlpha());
    }

    public function test_createInstanceWithAlpha() : void
    {
        $color = new RGBAColor(
            self::RED_PERCENT,
            self::GREEN_PERCENT,
            self::BLUE_PERCENT,
            self::ALPHA_PERCENT
        );

        // 84.15 -> 84 rounded down
        $this->assertSame(self::ALPHA_INTEGER, $color->getAlpha());
    }

    public function test_createInstanceGetPercent() : void
    {
        $color = new RGBAColor(self::RED_PERCENT, self::GREEN_PERCENT, self::BLUE_PERCENT, self::ALPHA_PERCENT);

        $this->assertSame(self::RED_INTEGER, $color->getRed());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());
        $this->assertSame(self::ALPHA_INTEGER, $color->getAlpha());
    }

    public function test_createFromArray() : void
    {
        $color = RGBAColor_Factory::createFromColor(array(
            RGBAColor::COMPONENT_RED => self::RED_INTEGER,
            RGBAColor::COMPONENT_GREEN => self::GREEN_INTEGER,
            RGBAColor::COMPONENT_BLUE => self::BLUE_INTEGER
        ));

        $this->assertSame(self::RED_INTEGER, $color->getRed());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());
        $this->assertSame(255, $color->getAlpha());
    }

    public function test_parseHEX() : void
    {
        $tests = array(
            array(
                'hex' => 'FFF',
                'expected' => array(255, 255, 255, 255)
            ),
            array(
                'hex' => '000',
                'expected' => array(0, 0, 0, 255)
            ),
            array(
                'hex' => 'FFFFFF',
                'expected' => array(255, 255, 255, 255)
            ),
            array(
                'hex' => 'ABDE0911',
                'expected' => array(hexdec('AB'), hexdec('DE'), hexdec('09'), hexdec('11'))
            )
        );

        foreach ($tests as $test)
        {
            $result = RGBAColor_Converter::hex2color($test['hex']);

            $this->assertSame($test['expected'][0], $result[RGBAColor::COMPONENT_RED]);
            $this->assertSame($test['expected'][1], $result[RGBAColor::COMPONENT_GREEN]);
            $this->assertSame($test['expected'][2], $result[RGBAColor::COMPONENT_BLUE]);
            $this->assertSame($test['expected'][3], $result[RGBAColor::COMPONENT_ALPHA]);
        }
    }

    /**
     * If the HEX color value is specified with a hash,
     * this should be ignored and work anyway.
     */
    public function test_createFromHEX_stripHash() : void
    {
        $this->assertSame('FFFFFF', RGBAColor_Factory::createFromHEX('#FFF')->toHEX());
    }

    public function test_createFromHEX3() : void
    {
        $color = RGBAColor_Factory::createFromHEX('FFF');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(255, $color->getGreen());
        $this->assertSame(255, $color->getBlue());
    }

    public function test_createFromHEX6() : void
    {
        $red = dechex(self::RED_INTEGER);
        $green = dechex(self::GREEN_INTEGER);
        $blue = dechex(self::BLUE_INTEGER);

        $color = RGBAColor_Factory::createFromHEX($red.$green.$blue);

        $this->assertSame(self::RED_INTEGER, $color->getRed());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());
    }

    public function test_createFromHEX6_caseInsensitive() : void
    {
        $red = dechex(self::RED_INTEGER);
        $green = strtoupper(dechex(self::GREEN_INTEGER));
        $blue = dechex(self::BLUE_INTEGER);

        $color = RGBAColor_Factory::createFromHEX($red.$green.$blue);

        $this->assertSame(self::RED_INTEGER, $color->getRed());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());
    }

    public function test_createFromHEX8() : void
    {
        $red = dechex(self::RED_INTEGER);
        $green = dechex(self::GREEN_INTEGER);
        $blue = dechex(self::BLUE_INTEGER);
        $alpha = dechex(self::ALPHA_INTEGER);

        $color = RGBAColor_Factory::createFromHEX($red.$green.$blue.$alpha);

        $this->assertSame(self::RED_INTEGER, $color->getRed());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());
        $this->assertSame(self::ALPHA_INTEGER, $color->getAlpha());
    }

    public function test_createFromInvalidHexLength() : void
    {
        $this->expectExceptionCode(RGBAColor::ERROR_INVALID_HEX_LENGTH);

        RGBAColor_Factory::createFromHEX('CCDEF');
    }

    public function test_createFromIndexedColor() : void
    {
        $color = RGBAColor_Factory::createFromIndexedColor(array(
            self::RED_INTEGER,
            self::GREEN_INTEGER,
            self::BLUE_INTEGER,
            self::ALPHA_INTEGER
        ));

        $this->assertSame(self::RED_INTEGER, $color->getRed());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());
        $this->assertSame(self::ALPHA_INTEGER, $color->getAlpha());
    }

    public function test_createFromPreset() : void
    {
        $this->assertEquals('FFFFFF', RGBAColor_Presets::white()->toHEX());
        $this->assertEquals('000000', RGBAColor_Presets::black()->toHEX());
        $this->assertEquals('00000000', RGBAColor_Presets::transparent()->toHEX());
    }

    public function test_transparency() : void
    {
        $transparent = RGBAColor_Presets::white()->setTransparency(100);

        $this->assertEquals(100, $transparent->getTransparency());
        $this->assertEquals(0, $transparent->getOpacity());

        $opaque = RGBAColor_Presets::white()->setTransparency(0);

        $this->assertEquals(0, $opaque->getTransparency());
        $this->assertEquals(100, $opaque->getOpacity());
    }

    public function test_opacity() : void
    {
        $transparent = RGBAColor_Presets::white()->setOpacity(0);

        $this->assertEquals(100, $transparent->getTransparency());
        $this->assertEquals(0, $transparent->getOpacity());

        $opaque = RGBAColor_Presets::white()->setOpacity(100);

        $this->assertEquals(0, $opaque->getTransparency());
        $this->assertEquals(100, $opaque->getOpacity());
    }

    public function test_createAuto_colorArray() : void
    {
        $color = RGBAColor_Factory::createAuto(array(
            RGBAColor::COMPONENT_RED => self::RED_INTEGER,
            RGBAColor::COMPONENT_GREEN => self::GREEN_INTEGER,
            RGBAColor::COMPONENT_BLUE => self::BLUE_INTEGER
        ));

        $this->assertSame(self::RED_INTEGER, $color->getRed());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());
    }

    public function test_createAuto_indexedColorArray() : void
    {
        $color = RGBAColor_Factory::createAuto(array(
            self::RED_INTEGER,
            self::GREEN_INTEGER,
            self::BLUE_INTEGER
        ));

        $this->assertSame(self::RED_INTEGER, $color->getRed());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());
    }

    public function test_createAuto_hexString() : void
    {
        $hex = RGBAColor_Converter::array2hex(array(
            RGBAColor::COMPONENT_RED => self::RED_INTEGER,
            RGBAColor::COMPONENT_GREEN => self::GREEN_INTEGER,
            RGBAColor::COMPONENT_BLUE => self::BLUE_INTEGER
        ));

        $color = RGBAColor_Factory::createAuto($hex);

        $this->assertSame(self::RED_INTEGER, $color->getRed());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue());
    }

    public function test_isValidColorArray() : void
    {
        $this->assertFalse(RGBAColor_Converter::isColorArray(array()));

        $this->assertFalse(RGBAColor_Converter::isColorArray(array(
            RGBAColor::COMPONENT_RED => self::RED_INTEGER,
            RGBAColor::COMPONENT_GREEN => self::GREEN_INTEGER
        )));

        $this->assertTrue(RGBAColor_Converter::isColorArray(array(
            RGBAColor::COMPONENT_RED => self::RED_INTEGER,
            RGBAColor::COMPONENT_GREEN => self::GREEN_INTEGER,
            RGBAColor::COMPONENT_BLUE => self::BLUE_INTEGER
        )));

        $this->assertTrue(RGBAColor_Converter::isColorArray(array(
            RGBAColor::COMPONENT_RED => self::RED_INTEGER,
            RGBAColor::COMPONENT_GREEN => self::GREEN_INTEGER,
            RGBAColor::COMPONENT_BLUE => self::BLUE_INTEGER,
            RGBAColor::COMPONENT_ALPHA => self::ALPHA_INTEGER
        )));
    }

    public function test_requireValidColorArray() : void
    {
        $this->expectExceptionCode(RGBAColor::ERROR_INVALID_COLOR_ARRAY);

        RGBAColor_Converter::requireValidColorArray(array());
    }
}
