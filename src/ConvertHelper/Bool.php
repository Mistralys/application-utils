<?php

declare(strict_types=1);

namespace AppUtils;

class ConvertHelper_Bool
{
    /**
     * @var array<mixed,bool>
     */
    protected static $booleanStrings = array(
        1 => true,
        0 => false,
        'true' => true,
        'false' => false,
        'yes' => true,
        'no' => false
    );

    /**
     * Converts a string, number or boolean value to a boolean value.
     *
     * @param mixed $string
     * @throws ConvertHelper_Exception
     * @return bool
     *
     * @see ConvertHelper::ERROR_INVALID_BOOLEAN_STRING
     */
    public static function fromString($string) : bool
    {
        if($string === '' || $string === null || !is_scalar($string))
        {
            return false;
        }

        if(is_bool($string))
        {
            return $string;
        }

        if(array_key_exists($string, self::$booleanStrings))
        {
            return self::$booleanStrings[$string];
        }

        throw new ConvertHelper_Exception(
            'Invalid string boolean representation',
            sprintf(
                'Cannot convert [%s] to a boolean value.',
                parseVariable($string)->enableType()->toString()
            ),
            ConvertHelper::ERROR_INVALID_BOOLEAN_STRING
        );
    }

    /**
     * Converts a boolean value to a string. Defaults to returning
     * 'true' or 'false', with the additional parameter it can also
     * return the 'yes' and 'no' variants.
     *
     * @param boolean|string $boolean
     * @param boolean $yesno
     * @return string
     * @throws ConvertHelper_Exception
     */
    public static function toString($boolean, bool $yesno = false) : string
    {
        // allow 'yes', 'true', 'no', 'false' string notations as well
        if(!is_bool($boolean)) {
            $boolean = self::fromString($boolean);
        }

        if ($boolean) {
            if ($yesno) {
                return 'yes';
            }

            return 'true';
        }

        if ($yesno) {
            return 'no';
        }

        return 'false';
    }

    /**
     * Checks if the specified string is a boolean value, which
     * includes string representations of boolean values, like
     * <code>yes</code> or <code>no</code>, and <code>true</code>
     * or <code>false</code>.
     *
     * @param mixed $value
     * @return boolean
     */
    public static function isBoolean($value) : bool
    {
        if(is_bool($value)) {
            return true;
        }

        if(!is_scalar($value)) {
            return false;
        }

        return array_key_exists($value, self::$booleanStrings);
    }
}
