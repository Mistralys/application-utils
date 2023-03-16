<?php
/**
 * @package Application Utils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\ColorChannel\SaturationChannel
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

/**
 * Color channel with values from 0 to 100.
 *
 * Native value: {@see self::getPercent()} and
 * {@see self::getPercentRounded()}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class SaturationChannel extends PercentChannel
{

}
