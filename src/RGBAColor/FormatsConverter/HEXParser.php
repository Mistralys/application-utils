<?php
/**
 * File containing the class {@see \AppUtils\RGBAColor\FormatsConverter\HEXParser}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\FormatsConverter\HEXParser
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor\FormatsConverter;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorException;
use AppUtils\RGBAColor\ColorFactory;
use AppUtils\RGBAColor\ColorChannel;

/**
 * Specialized hexadecimal color string parser.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HEXParser
{
    /**
     * Converts the HEX color string to an 8-Bit color array.
     *
     * @param string $hex
     * @param string $name
     * @return RGBAColor
     *
     * @throws ColorException
     * @see RGBAColor::ERROR_INVALID_HEX_LENGTH
     */
    public function parse(string $hex, string $name='') : RGBAColor
    {
        $hex = ltrim($hex, '#'); // Remove the hash if present
        $hex = strtoupper($hex);
        $length = strlen($hex);

        if($length === 3)
        {
            return $this->parseHEX3($hex, $name);
        }

        if($length === 6)
        {
            return $this->parseHEX6($hex, $name);
        }

        if ($length === 8)
        {
            return $this->parseHEX8($hex, $name);
        }

        throw new ColorException(
            'Invalid HEX color value.',
            sprintf(
                'The hex string [%s] has an invalid length ([%s] characters). '.
                'It must be either 6 characters (RRGGBB) or 8 characters (RRGGBBAA) long.',
                $hex,
                $length
            ),
            RGBAColor::ERROR_INVALID_HEX_LENGTH
        );
    }

    /**
     * Parses a three-letter HEX color string to a color array.
     *
     * @param string $hex
     * @param string $name
     * @return RGBAColor
     */
    private function parseHEX3(string $hex, string $name) : RGBAColor
    {
        return ColorFactory::create(
            ColorChannel::hexadecimal($hex[0]),
            ColorChannel::hexadecimal($hex[1]),
            ColorChannel::hexadecimal($hex[2]),
            null,
            $name
        );
    }

    /**
     * Parses a six-letter HEX color string to a color array.
     * @param string $hex
     * @param string $name
     * @return RGBAColor
     * @throws ColorException
     */
    private function parseHEX6(string $hex, string $name) : RGBAColor
    {
        return ColorFactory::create(
            ColorChannel::hexadecimal(substr($hex, 0, 2)),
            ColorChannel::hexadecimal(substr($hex, 2, 2)),
            ColorChannel::hexadecimal(substr($hex, 4, 2)),
            null,
            $name
        );
    }

    /**
     * Parses an eight-letter HEX color string (with alpha) to a color array.
     * @param string $hex
     * @param string $name
     * @return RGBAColor
     * @throws ColorException
     */
    private function parseHEX8(string $hex, string $name) : RGBAColor
    {
        return ColorFactory::create(
            ColorChannel::hexadecimal(substr($hex, 0, 2)),
            ColorChannel::hexadecimal(substr($hex, 2, 2)),
            ColorChannel::hexadecimal(substr($hex, 4, 2)),
            ColorChannel::hexadecimal(substr($hex, 6, 2)),
            $name
        );
    }
}
