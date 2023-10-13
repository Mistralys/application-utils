<?php

declare(strict_types=1);

namespace AppUtils;

/**
 * Parses the specified number, and returns a NumberInfo instance.
 *
 * @param NumberInfo|string|int|float|NULL $value
 * @param bool $forceNew
 * @return NumberInfo
 */
function parseNumber($value, bool $forceNew=false) : NumberInfo
{
    if($value instanceof NumberInfo && $forceNew !== true) {
        return $value;
    }
    
    return new NumberInfo($value);
}

/**
 * Like {@see parseNumber()}, but returns an immutable
 * instance where any operations that modify the value
 * return a new instance, leaving the original instance
 * intact.
 *
 * @param NumberInfo|string|int|float|NULL $value
 * @return NumberInfo_Immutable
 */
function parseNumberImmutable($value) : NumberInfo_Immutable
{
    return new NumberInfo_Immutable($value);
}

/**
 * Creates a boolean value.
 * 
 * @param bool $initial The initial boolean value to use.
 * @return Value_Bool
 */
function valBool(bool $initial=false) : Value_Bool
{
    return new Value_Bool($initial);
}

/**
 * Creates a sticky true-based boolean value: starts out
 * as false, and if it is set to true, cannot be set to
 * false again afterwards.
 * 
 * @param bool $initial
 * @return Value_Bool_True
 */
function valBoolTrue(bool $initial=false) : Value_Bool_True
{
    return new Value_Bool_True($initial);
}

/**
 * Creates a sticky false-based boolean value: starts out
 * as true, and if it is set to false, cannot be set to
 * true again afterwards.
 * 
 * @param bool $initial
 * @return Value_Bool_False
 */
function valBoolFalse(bool $initial=true) : Value_Bool_False
{
    return new Value_Bool_False($initial);
}

/**
 * Whether the current request is run via the command line.
 * @return bool
 */
function isCLI() : bool
{
    return PHP_SAPI === "cli";
}

/**
 * Removes the specified values from the target array.
 *
 * @param array<mixed> $haystack
 * @param array<mixed> $values
 * @param bool $strict
 * @return array<mixed>
 */
function array_remove_values(array $haystack, array $values, bool $strict=true) : array
{
    return array_filter(
        $haystack,
        static fn($entry) => !in_array($entry, $values, $strict)
    );
}
