<?php

namespace AppUtils;

/**
 * Parses the specified number, and returns a NumberInfo instance.
 *
 * @param mixed $value
 * @return \AppUtils\NumberInfo
 */
function parseNumber($value, $forceNew=false)
{
    if($value instanceof NumberInfo && $forceNew !== true) {
        return $value;
    }
    
    return new NumberInfo($value);
}

/**
 * Parses the specified variable, and allows accessing
 * information on it.
 * 
 * @param mixed $variable
 * @return \AppUtils\VariableInfo
 */
function parseVariable($variable)
{
    return new VariableInfo($variable);
}