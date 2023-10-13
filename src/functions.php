<?php

declare(strict_types=1);

namespace AppUtils;

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
