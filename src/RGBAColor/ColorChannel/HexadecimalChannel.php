<?php
/**
 * File containing the class {@see \AppUtils\RGBAColor\ColorChannel\HexadecimalChannel}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\ColorChannel\HexadecimalChannel
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorException;

/**
 * The hexadecimal is actually an eight bit channel,
 * and can be used as one, as it extends the {@see EightBitChannel}
 * class.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HexadecimalChannel extends EightBitChannel
{
    public const ERROR_INVALID_HEX_VALUE = 103001;

    /**
     * @param string $value A double or single hex character. e.g. "FF".
     *                      For a single character, it is assumed it should
     *                      be duplicated. Example: "F" > "FF".
     * @throws ColorException
     */
    public function __construct(string $value)
    {
        $this->validateValue($value);

        if(strlen($value) === 1)
        {
            $value = str_repeat($value, 2);
        }

        parent::__construct((int)hexdec($value));
    }

    /**
     * @param string $hex
     * @return void
     * @throws ColorException
     */
    private function validateValue(string $hex) : void
    {
        if(preg_match('/[0-9A-F]{1,2}/i', $hex) !== false)
        {
            return;
        }

        throw new ColorException(
            'Invalid hexadecimal value.',
            sprintf(
                'The value [%s] is not a valid color hex value.',
                $hex
            ),
            self::ERROR_INVALID_HEX_VALUE
        );
    }
}
