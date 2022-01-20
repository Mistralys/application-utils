<?php

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor;

class ArrayConverter
{
    /**
     * @var RGBAColor
     */
    private $color;

    public function __construct(RGBAColor $color)
    {
        $this->color = $color;
    }

    public function eightBit() : array
    {
        return array(
            RGBAColor::CHANNEL_RED => $this->color->getRed()->get8Bit(),
            RGBAColor::CHANNEL_GREEN => $this->color->getGreen()->get8Bit(),
            RGBAColor::CHANNEL_BLUE => $this->color->getBlue()->get8Bit(),
            RGBAColor::CHANNEL_ALPHA => $this->color->getOpacity()->get8Bit()
        );
    }

    public function percent() : array
    {
        return array(
            RGBAColor::CHANNEL_RED => $this->color->getRed()->getPercent(),
            RGBAColor::CHANNEL_GREEN => $this->color->getGreen()->getPercent(),
            RGBAColor::CHANNEL_BLUE => $this->color->getBlue()->getPercent(),
            RGBAColor::CHANNEL_ALPHA => $this->color->getOpacity()->getPercent()
        );
    }

    public function GD() : array
    {
        return array(
            RGBAColor::CHANNEL_RED => $this->color->getRed()->get8Bit(),
            RGBAColor::CHANNEL_GREEN => $this->color->getGreen()->get8Bit(),
            RGBAColor::CHANNEL_BLUE => $this->color->getBlue()->get8Bit(),
            RGBAColor::CHANNEL_ALPHA => $this->color->getOpacity()->get7Bit()
        );
    }

    public function CSS() : array
    {
        return array(
            RGBAColor::CHANNEL_RED => $this->color->getRed()->get8Bit(),
            RGBAColor::CHANNEL_GREEN => $this->color->getGreen()->get8Bit(),
            RGBAColor::CHANNEL_BLUE => $this->color->getBlue()->get8Bit(),
            RGBAColor::CHANNEL_ALPHA => $this->color->getOpacity()->getFloat()
        );
    }
}
