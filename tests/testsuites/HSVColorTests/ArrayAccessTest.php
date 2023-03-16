<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites\HSVColorTests;

use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;

final class ArrayAccessTest extends TestCase
{
    public function test_accessValues() : void
    {
        $hsv = ColorFactory::createHSV(303, 88, 33);

        $this->assertSame(303.0, $hsv['hue']);
        $this->assertSame(88.0, $hsv['saturation']);
        $this->assertSame(33.0, $hsv['brightness']);
    }

    public function test_accessUnknownValue() : void
    {
        $hsv = ColorFactory::createHSV(303, 88, 33);

        $this->assertSame(0.0, $hsv['unknown_value']);
    }

    public function test_ignoreSetValue() : void
    {
        $hsv = ColorFactory::createHSV(303, 88, 33);

        $this->assertSame(303.0, $hsv['hue']);

        $hsv['hue'] = 200.0;

        $this->assertSame(303.0, $hsv['hue']);
    }

    public function test_ignoreUnsetValue() : void
    {
        $hsv = ColorFactory::createHSV(303, 88, 33);

        $this->assertSame(303.0, $hsv['hue']);

        unset($hsv['hue']);

        $this->assertSame(303.0, $hsv['hue']);
    }
}