<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\ColorChannel\EightBitChannel;
use AppUtils\RGBAColor\ColorFactory;
use TestClasses\BaseTestCase;

class ColorArrayAccessTest extends BaseTestCase
{
    public function test_access() : void
    {
        $color = ColorFactory::preset()->white();

        $this->assertInstanceOf(EightBitChannel::class, $color['red']);
    }

    /**
     * Unsetting a color channel must be ignored.
     */
    public function test_unset() : void
    {
        $color = ColorFactory::preset()->white();

        $red = $color['red'];

        unset($color['red']);

        $this->assertSame($red, $color['red']);
    }

    /**
     * The {@see RGBAColor::setColor()} method used internally to
     * set an offset expects a {@see ColorChannel} value.
     */
    public function test_setInvalidValue() : void
    {
        $color = ColorFactory::preset()->white();

        // The error message is different between PHP versions;
        // This is the common denominator, which is enough to
        // check if the error happened.
        $this->expectExceptionMessageMatches('/must be/');

        $color['red'] = 148;
    }

    public function test_setValid() : void
    {
        $color = ColorFactory::preset()->white();

        $color['blue'] = ColorChannel::eightBit(42);

        $this->assertSame($color['blue']->get8Bit(), 42);
    }

    public function test_setInvalidChannel() : void
    {
        $color = ColorFactory::preset()->white();

        $this->expectExceptionCode(RGBAColor::ERROR_INVALID_COLOR_COMPONENT);

        $color['unknown'] = ColorChannel::eightBit(42);
    }
}
