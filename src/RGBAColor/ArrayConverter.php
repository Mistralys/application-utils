<?php
/**
 * @package AppUtils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\ArrayConverter
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor;

use AppUtils\RGBAColor;

/**
 * Utility class used to convert an RGB color to
 * different array constructs depending on the use
 * cases.
 *
 * An instance of this class is returned by the
 * method {@see RGBAColor::toArray()}.
 *
 * @package AppUtils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ArrayConverter
{
    /**
     * @var RGBAColor
     */
    private RGBAColor $color;

    public function __construct(RGBAColor $color)
    {
        $this->color = $color;
    }

    /**
     * @return array<string,int>
     */
    public function eightBit() : array
    {
        return array(
            RGBAColor::CHANNEL_RED => $this->color->getRed()->get8Bit(),
            RGBAColor::CHANNEL_GREEN => $this->color->getGreen()->get8Bit(),
            RGBAColor::CHANNEL_BLUE => $this->color->getBlue()->get8Bit(),
            RGBAColor::CHANNEL_ALPHA => $this->color->getAlpha()->get8Bit()
        );
    }

    /**
     * @return array<string,float>
     */
    public function percent() : array
    {
        return array(
            RGBAColor::CHANNEL_RED => $this->color->getRed()->getPercent(),
            RGBAColor::CHANNEL_GREEN => $this->color->getGreen()->getPercent(),
            RGBAColor::CHANNEL_BLUE => $this->color->getBlue()->getPercent(),
            RGBAColor::CHANNEL_ALPHA => $this->color->getAlpha()->getPercent()
        );
    }

    /**
     * @return array<string,int>
     */
    public function GD() : array
    {
        return array(
            RGBAColor::CHANNEL_RED => $this->color->getRed()->get8Bit(),
            RGBAColor::CHANNEL_GREEN => $this->color->getGreen()->get8Bit(),
            RGBAColor::CHANNEL_BLUE => $this->color->getBlue()->get8Bit(),
            RGBAColor::CHANNEL_ALPHA => $this->color->getAlpha()->get7Bit()
        );
    }

    /**
     * @return array<string,int|float>
     */
    public function CSS() : array
    {
        return array(
            RGBAColor::CHANNEL_RED => $this->color->getRed()->get8Bit(),
            RGBAColor::CHANNEL_GREEN => $this->color->getGreen()->get8Bit(),
            RGBAColor::CHANNEL_BLUE => $this->color->getBlue()->get8Bit(),
            RGBAColor::CHANNEL_ALPHA => $this->color->getAlpha()->getAlpha()
        );
    }
}
