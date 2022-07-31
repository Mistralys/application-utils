<?php

declare(strict_types=1);

namespace StyleCollectionTests;

use AppUtils\RGBAColor\ColorChannel\HexadecimalChannel;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\StyleCollection\StyleBuilder;
use PHPUnit\Framework\TestCase;

class ColorStyleTest extends TestCase
{
    public function test_hexString() : void
    {
        $this->assertEquals(
            'color:#FFFFFF',
            (string)StyleBuilder::create()
                ->color()->hexString('fff')
        );
    }

    public function test_hexStringWithHash() : void
    {
        $this->assertEquals(
            'color:#FFFFFF',
            (string)StyleBuilder::create()
                ->color()->hexString('#fff')
        );
    }

    public function test_hexStringWithAlpha() : void
    {
        $this->assertEquals(
            'color:#CCCCCCAA',
            (string)StyleBuilder::create()
                ->color()->hexString('#CCCCCCAA')
        );
    }

    public function test_hexStringInvalidException() : void
    {
        $this->expectExceptionCode(HexadecimalChannel::ERROR_INVALID_HEX_VALUE);

        StyleBuilder::create()->color()->hexString('PPP');
    }

    public function test_hex() : void
    {
        $this->assertEquals(
            'color:#FFFFFF',
            (string)StyleBuilder::create()
                ->color()->hex(ColorFactory::preset()->white())
        );
    }

    public function test_rgba() : void
    {
        $this->assertEquals(
            'color:rgba(255, 255, 255, 0.2)',
            (string)StyleBuilder::create()
                ->color()->rgba(ColorFactory::createCSS(255, 255, 255, 0.2))
        );
    }

    public function test_rgbaValues() : void
    {
        $this->assertEquals(
            'color:rgba(255, 255, 255, 0.4)',
            (string)StyleBuilder::create()
                ->color()->rgbaValues(255, 255, 255, 0.4)
        );
    }

    public function test_rgb() : void
    {
        $this->assertEquals(
            'color:rgb(255, 255, 255)',
            (string)StyleBuilder::create()
                ->color()->rgba(ColorFactory::createCSS(255, 255, 255))
        );
    }

    public function test_rgbValues() : void
    {
        $this->assertEquals(
            'color:rgb(255, 255, 255)',
            (string)StyleBuilder::create()
                ->color()->rgbaValues(255, 255, 255)
        );
    }

    public function test_transparent() : void
    {
        $this->assertEquals(
            'color:rgba(0, 0, 0, 1)',
            (string)StyleBuilder::create()
                ->color()->transparent()
        );
    }

    public function test_combineColors() : void
    {
        $this->assertEquals(
            'background-color:rgb(0, 0, 0);color:rgba(0, 0, 0, 1)',
            (string)StyleBuilder::create()
                ->color()->transparent()
                ->background()->color()->black()
        );
    }
}
