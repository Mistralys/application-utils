<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorFactory;
use PHPUnit\Framework\TestCase;

final class ColorLuminanceTest extends TestCase
{
    public function test_isDarkIsLight() : void
    {
        $tests = array(
            array(
                'label' => 'Light color',
                'rgb' => array(219, 237, 248),
                'dark' => false,
                'luma' => 92,
                '8Bit' => 234
            ),
            array(
                'label' => 'Medium dark color',
                'rgb' => array(20, 116, 196),
                'dark' => true,
                'luma' => 40,
                '8Bit' => 101
            ),
            array(
                'label' => 'Darker color',
                'rgb' => array(0, 61, 143),
                'dark' => true,
                'luma' => 21,
                '8Bit' => 54
            ),
            array(
                'label' => 'Full black',
                'rgb' => array(0, 0, 0),
                'dark' => true,
                'luma' => 0,
                '8Bit' => 0
            ),
            array(
                'label' => 'Full white',
                'rgb' => array(255, 255, 255),
                'dark' => false,
                'luma' => 100,
                '8Bit' => 255
            )
        );

        foreach($tests as $test)
        {
            $color = ColorFactory::create8Bit($test['rgb'][0], $test['rgb'][1], $test['rgb'][2]);

            $label = PHP_EOL.
                $test['label'].PHP_EOL.
                'Color......: '.$color->toCSS().PHP_EOL.
                'Luma %.....: '.$color->getLumaPercent().'%'.PHP_EOL.
                'Luma 8bit..: '.$color->getLuma();

            $this->assertSame($test['dark'], $color->isDark(), 'Color should be considered dark. '.$label);
            $this->assertSame(!$test['dark'], $color->isLight(), 'Color should not be considered light. '.$label);
            $this->assertSame($test['luma'], (int)round($color->getLumaPercent()), 'Luma percentage does not match the expected value. '.$label);
            $this->assertSame($test['8Bit'], $color->getLuma(), 'Luma 8Bit does not match the expected value. '.$label);
        }
    }

    public function test_adjustLumaThreshold() : void
    {
        $color = ColorFactory::createAuto(array(20, 116, 196));

        // This color has a Luma of 40%
        $this->assertSame(40, (int)round($color->getLumaPercent()));

        // It is considered dark with the default setting,
        // which is 50% or less Luma.
        $this->assertTrue($color->isDark());

        // Decrease the default Luma so colors need to have
        // 30% Luma or less.
        RGBAColor::setDarkLumaThreshold(30);

        // Now the color must not be considered dark anymore,
        // as it is at 40% Luma.
        $this->assertFalse($color->isDark());
    }

    // region: Support methods

    protected function setUp(): void
    {
        parent::setUp();

        // Reset the Luma threshold
        RGBAColor::setDarkLumaThreshold(RGBAColor::DEFAULT_LUMA_THRESHOLD);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset the Luma threshold
        RGBAColor::setDarkLumaThreshold(RGBAColor::DEFAULT_LUMA_THRESHOLD);
    }
}
