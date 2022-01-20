<?php

declare(strict_types=1);

use AppUtils\RGBAColor\ColorChannel;
use PHPUnit\Framework\TestCase;

class ColorChannelsTest extends TestCase
{
    public function test_invert() : void
    {
        $this->assertSame(200, ColorChannel::EightBit(55)->invert()->get8Bit());
        $this->assertSame(100, ColorChannel::SevenBit(27)->invert()->get7Bit());
        $this->assertSame(45.5, ColorChannel::Percent(54.5)->invert()->getPercent());
        $this->assertSame(0.45, ColorChannel::CSSOpacity(0.55)->invert()->getFloat());
    }

    public function test_8bit_fullSaturation() : void
    {
        $channel = ColorChannel::EightBit(255);

        $this->assertSame(255, $channel->get8Bit());
        $this->assertSame(127, $channel->get7Bit());
        $this->assertSame(100.0, $channel->getPercent());
        $this->assertSame(1.0, $channel->getFloat());
    }

    public function test_8bit_partialSaturation() : void
    {
        $value = 78;
        $channel = ColorChannel::EightBit($value);

        $this->assertSame($value, $channel->get8Bit());
        $this->assertSame((int)round($value * 127 / 255), $channel->get7Bit());
        $this->assertSame($value * 100 / 255, $channel->getPercent());
        $this->assertSame(round($value / 255, 2), $channel->getFloat());
    }

    public function test_7bit_fullSaturation() : void
    {
        $channel = ColorChannel::SevenBit(127);

        $this->assertSame(255, $channel->get8Bit());
        $this->assertSame(127, $channel->get7Bit());
        $this->assertSame(100.0, $channel->getPercent());
        $this->assertSame(1.0, $channel->getFloat());
    }

    public function test_7bit_partialSaturation() : void
    {
        $value = 78;
        $channel = ColorChannel::SevenBit($value);

        $this->assertSame((int)round($value * 255 / 127), $channel->get8Bit());
        $this->assertSame($value, $channel->get7Bit());
        $this->assertSame($value * 100 / 127, $channel->getPercent());
        $this->assertSame(round($value / 127, 2), $channel->getFloat());
    }

    public function test_percent_fullSaturation() : void
    {
        $channel = ColorChannel::Percent(100);

        $this->assertSame(255, $channel->get8Bit());
        $this->assertSame(127, $channel->get7Bit());
        $this->assertSame(100.0, $channel->getPercent());
        $this->assertSame(1.0, $channel->getFloat());
    }

    public function test_percent_partialSaturation() : void
    {
        $value = 78.6;
        $channel = ColorChannel::Percent($value);

        $this->assertSame((int)round($value * 255 / 100), $channel->get8Bit());
        $this->assertSame((int)round($value * 127 / 100), $channel->get7Bit());
        $this->assertSame($value, $channel->getPercent());
        $this->assertSame(round($value / 100, 2), $channel->getFloat());
    }
}
