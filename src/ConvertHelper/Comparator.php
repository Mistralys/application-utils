<?php
/**
 * File containing the {@see \AppUtils\ConvertHelper_Comparator} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see \AppUtils\ConvertHelper_Comparator
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Comparison helper for different variable types.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_Comparator
{
    /**
     * Checks whether the specified variables are equal (exact type check by default).
     *
     * @param mixed $a
     * @param mixed $b
     * @return bool
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE
     */
    public static function areVariablesEqual($a, $b) : bool
    {
        $a = self::convertScalarForComparison($a);
        $b = self::convertScalarForComparison($b);

        return $a === $b;
    }

    /**
     * Converts any scalar value to a string for comparison purposes.
     *
     * @param mixed|null $scalar
     * @return string|null
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE
     */
    protected static function convertScalarForComparison($scalar) : ?string
    {
        if($scalar === '' || is_null($scalar)) {
            return null;
        }

        if(is_bool($scalar)) {
            return ConvertHelper_Bool::toStringStrict($scalar);
        }

        if(is_array($scalar)) {
            $scalar = md5(serialize($scalar));
        }

        if($scalar !== null && !is_scalar($scalar)) {
            throw new ConvertHelper_Exception(
                'Not a scalar value in comparison',
                null,
                ConvertHelper::ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE
            );
        }

        return strval($scalar);
    }

    /**
     * Compares two strings to check whether they are equal.
     * null and empty strings are considered equal.
     *
     * @param string|NULL $a
     * @param string|NULL $b
     * @return boolean
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE
     */
    public static function areStringsEqual(?string $a, ?string $b) : bool
    {
        return self::areVariablesEqual($a, $b);
    }

    /**
     * Checks whether the two specified numbers are equal.
     * null and empty strings are considered as 0 values.
     *
     * @param number|string|NULL $a
     * @param number|string|NULL $b
     * @return boolean
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_CANNOT_NORMALIZE_NON_SCALAR_VALUE
     */
    public static function areNumbersEqual($a, $b) : bool
    {
        return self::areVariablesEqual($a, $b);
    }
}
