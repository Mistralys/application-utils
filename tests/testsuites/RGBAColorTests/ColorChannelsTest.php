<?php

declare(strict_types=1);

namespace RGBAColorTests;

use AppUtils\RGBAColor\ColorChannel;
use PHPUnit\Framework\TestCase;

class ColorChannelsTest extends TestCase
{
    public function test_invert() : void
    {
        $this->assertSame(200, ColorChannel::eightBit(55)->invert()->get8Bit());
        $this->assertSame(100, ColorChannel::sevenBit(27)->invert()->get7Bit());
        $this->assertSame(45.5, ColorChannel::percent(54.5)->invert()->getPercent());
        $this->assertSame(0.45, ColorChannel::decimal(0.55)->invert()->getDecimal());
    }

    public function test_8bit_fullSaturation() : void
    {
        $channel = ColorChannel::eightBit(255);

        $this->assertSame(255, $channel->get8Bit());
        $this->assertSame(127, $channel->get7Bit());
        $this->assertSame(100.0, $channel->getPercent());
        $this->assertSame(1.0, $channel->getDecimal());
    }

    public function test_8bit_partialSaturation() : void
    {
        $value = 78;
        $channel = ColorChannel::eightBit($value);

        $this->assertSame($value, $channel->get8Bit());
        $this->assertSame((int)round($value * 127 / 255), $channel->get7Bit());
        $this->assertSame($value * 100 / 255, $channel->getPercent());
        $this->assertSame(round($value / 255, 2), $channel->getDecimal());
    }

    public function test_7bit_fullSaturation() : void
    {
        $channel = ColorChannel::sevenBit(127);

        $this->assertSame(255, $channel->get8Bit());
        $this->assertSame(127, $channel->get7Bit());
        $this->assertSame(100.0, $channel->getPercent());
        $this->assertSame(1.0, $channel->getDecimal());
    }

    public function test_7bit_partialSaturation() : void
    {
        $value = 78;
        $channel = ColorChannel::sevenBit($value);

        $this->assertSame((int)round($value * 255 / 127), $channel->get8Bit());
        $this->assertSame($value, $channel->get7Bit());
        $this->assertSame($value * 100 / 127, $channel->getPercent());
        $this->assertSame(round($value / 127, 2), $channel->getDecimal());
    }

    public function test_percent_fullSaturation() : void
    {
        $channel = ColorChannel::percent(100);

        $this->assertSame(255, $channel->get8Bit());
        $this->assertSame(127, $channel->get7Bit());
        $this->assertSame(100.0, $channel->getPercent());
        $this->assertSame(1.0, $channel->getDecimal());
    }

    public function test_percent_partialSaturation() : void
    {
        $value = 78.6;
        $channel = ColorChannel::percent($value);

        $this->assertSame((int)round($value * 255 / 100), $channel->get8Bit());
        $this->assertSame((int)round($value * 127 / 100), $channel->get7Bit());
        $this->assertSame($value, $channel->getPercent());
        $this->assertSame(round($value / 100, 2), $channel->getDecimal());
    }
}
