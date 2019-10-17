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

/**
 * Translation function used to translate some of the internal
 * strings: if the localization is installed, it will use this
 * to do the translation.
 * 
 * @return string
 */
function t()
{
    $args = func_get_args();
    
    // is the localization package installed?
    if(class_exists('\AppLocalize\Localization')) 
    {
        return call_user_func_array('\AppLocalize\t', $args);
    }
    
    // simulate the translation function
    return call_user_func_array('sprintf', $args);
}
