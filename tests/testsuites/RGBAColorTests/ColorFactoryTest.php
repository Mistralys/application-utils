<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\RGBAColor\FormatsConverter;
use AppUtils\RGBAColor\ColorPresets;
use AppUtils\RGBAColor\PresetsManager;
use PHPUnit\Framework\TestCase;

class ColorFactoryTest extends TestCase
{
    public const RED_PERCENT = 10.45;
    public const GREEN_PERCENT = 45.78;
    public const BLUE_PERCENT = 99.10;
    public const RED_INTEGER = 27;
    public const GREEN_INTEGER = 117;
    public const BLUE_INTEGER = 253;
    public const ALPHA_INTEGER = 84;
    public const ALPHA_PERCENT = 33;
    
    public function test_createAuto_colorArray() : void
    {
        $color = ColorFactory::createAuto(array(
            RGBAColor::CHANNEL_RED => self::RED_INTEGER,
            RGBAColor::CHANNEL_GREEN => self::GREEN_INTEGER,
            RGBAColor::CHANNEL_BLUE => self::BLUE_INTEGER
        ));

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
    }

    public function test_createAuto_colorArrayIndexed() : void
    {
        $color = ColorFactory::createAuto(array(
            self::RED_INTEGER,
            self::GREEN_INTEGER,
            self::BLUE_INTEGER
        ));

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
    }

    public function test_createAuto_presetName() : void
    {
        $color = ColorFactory::createAuto(PresetsManager::COLOR_BLACK);

        $this->assertSame(PresetsManager::COLOR_BLACK, $color->getName());
        $this->assertSame(0, $color->getRed()->get8Bit());
        $this->assertSame(0, $color->getGreen()->get8Bit());
        $this->assertSame(0, $color->getBlue()->get8Bit());
    }

    public function test_createAuto_hexString3() : void
    {
        $color = ColorFactory::createAuto('CEF');

        $this->assertSame(hexdec('CC'), $color->getRed()->get8Bit());
        $this->assertSame(hexdec('EE'), $color->getGreen()->get8Bit());
        $this->assertSame(hexdec('FF'), $color->getBlue()->get8Bit());
    }

    public function test_createAuto_hexString6() : void
    {
        $hex =
            dechex(self::RED_INTEGER).
            dechex(self::GREEN_INTEGER).
            dechex(self::BLUE_INTEGER);

        $color = ColorFactory::createAuto($hex);

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
    }

    public function test_createAuto_hexString8() : void
    {
        $hex =
            dechex(self::RED_INTEGER).
            dechex(self::GREEN_INTEGER).
            dechex(self::BLUE_INTEGER).
            dechex(self::ALPHA_INTEGER);

        $color = ColorFactory::createAuto($hex);

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
        $this->assertSame(self::ALPHA_INTEGER, $color->getOpacity()->get8Bit());
    }

    public function test_createFromArray() : void
    {
        $color = ColorFactory::createFrom8BitArray(array(
            RGBAColor::CHANNEL_RED => self::RED_INTEGER,
            RGBAColor::CHANNEL_GREEN => self::GREEN_INTEGER,
            RGBAColor::CHANNEL_BLUE => self::BLUE_INTEGER
        ));

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
        $this->assertSame(255, $color->getOpacity()->get8Bit());
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
            $color = FormatsConverter::hex2color($test['hex']);

            $this->assertSame($test['expected'][0], $color->getRed()->get8Bit());
            $this->assertSame($test['expected'][1], $color->getGreen()->get8Bit());
            $this->assertSame($test['expected'][2], $color->getBlue()->get8Bit());
            $this->assertSame($test['expected'][3], $color->getOpacity()->get8Bit());
        }
    }

    /**
     * If the HEX color value is specified with a hash,
     * this should be ignored and work anyway.
     */
    public function test_createFromHEX_stripHash() : void
    {
        $this->assertSame(
            'FFFFFF',
            ColorFactory::createFromHEX('#FFF')->toHEX()
        );
    }

    public function test_createFromHEX3() : void
    {
        $color = ColorFactory::createFromHEX('FFF');

        $this->assertSame(255, $color->getRed()->get8Bit());
        $this->assertSame(255, $color->getGreen()->get8Bit());
        $this->assertSame(255, $color->getBlue()->get8Bit());
    }

    public function test_createFromHEX6() : void
    {
        $red = dechex(self::RED_INTEGER);
        $green = dechex(self::GREEN_INTEGER);
        $blue = dechex(self::BLUE_INTEGER);

        $color = ColorFactory::createFromHEX($red.$green.$blue);

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
    }

    public function test_createFromHEX6_caseInsensitive() : void
    {
        $red = dechex(self::RED_INTEGER);
        $green = strtoupper(dechex(self::GREEN_INTEGER));
        $blue = dechex(self::BLUE_INTEGER);

        $color = ColorFactory::createFromHEX($red.$green.$blue);

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
    }

    public function test_createFromHEX8() : void
    {
        $red = dechex(self::RED_INTEGER);
        $green = dechex(self::GREEN_INTEGER);
        $blue = dechex(self::BLUE_INTEGER);
        $alpha = dechex(self::ALPHA_INTEGER);

        $color = ColorFactory::createFromHEX($red.$green.$blue.$alpha);

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
        $this->assertSame(self::ALPHA_INTEGER, $color->getOpacity()->get8Bit());
    }

    public function test_createFromInvalidHexLength() : void
    {
        $this->expectExceptionCode(RGBAColor::ERROR_INVALID_HEX_LENGTH);

        ColorFactory::createFromHEX('CCDEF');
    }

    public function test_createFromArray_withAlpha() : void
    {
        $color = ColorFactory::createFrom8BitArray(array(
            self::RED_INTEGER,
            self::GREEN_INTEGER,
            self::BLUE_INTEGER,
            self::ALPHA_INTEGER
        ));

        $this->assertSame(self::RED_INTEGER, $color->getRed()->get8Bit());
        $this->assertSame(self::GREEN_INTEGER, $color->getGreen()->get8Bit());
        $this->assertSame(self::BLUE_INTEGER, $color->getBlue()->get8Bit());
        $this->assertSame(self::ALPHA_INTEGER, $color->getOpacity()->get8Bit());
    }

    public function test_createFromPreset() : void
    {
        $this->assertEquals('FFFFFF', ColorPresets::white()->toHEX());
        $this->assertEquals('000000', ColorPresets::black()->toHEX());
        $this->assertEquals('00000000', ColorPresets::transparent()->toHEX());
    }
}
