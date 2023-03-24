<?php

declare(strict_types=1);

namespace AppUtilsTests\TestSuites\HSVColorTests;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;

final class CoreTest extends TestCase
{
    /**
     * Ensure that when converting to HSV and back,
     * the alpha channel is preserved.
     */
    public function test_alphaPassThrough() : void
    {
        $rgb = ColorFactory::create(
            ColorChannel::eightBit(145),
            ColorChannel::eightBit(78),
            ColorChannel::eightBit(39),
            ColorChannel::alpha(0.6)
        );

        $hsv = $rgb->toHSV();

        $this->assertSame(0.6, $hsv->getAlpha()->getValue());

        $rgb = $hsv->toRGB();

        $this->assertSame(0.6, $rgb->getAlpha()->getAlpha());
    }

    public function test_isColorDark() : void
    {
        $tests = array(
            array(
                'label' => 'Light color',
                'hex' => 'ccc',
                'dark' => false
            ),
            array(
                'label' => 'Exactly 50% luminosity',
                'hex' => '808080',
                'dark' => false
            ),
            array(
                'label' => 'Slightly below 50% luminosity',
                'hex' => '7f7f7f',
                'dark' => true
            ),
            array(
                'label' => 'Black',
                'hex' => '000',
                'dark' => true
            ),
            array(
                'label' => 'White',
                'hex' => 'fff',
                'dark' => false
            )
        );

        foreach($tests as $test)
        {
            $color = ColorFactory::createFromHEX($test['hex']);
            $brightness = $color->getBrightness();

            $this->assertSame(
                $brightness->getPercent() < 50,
                $test['dark'],
                'Test label: '.$test['label'].PHP_EOL.
                'Brightness: '.$brightness->getPercent()
            );
        }
    }
}
