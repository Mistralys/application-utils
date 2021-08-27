<?php

namespace AppUtils;

use DateInterval;
use Throwable;

/**
 * Parses the specified number, and returns a NumberInfo instance.
 *
 * @param NumberInfo|string|int|float|NULL $value
 * @param bool $forceNew
 * @return NumberInfo
 */
function parseNumber($value, bool $forceNew=false)
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
 * Parses the specified variable, and allows accessing
 * information on it.
 * 
 * @param mixed $variable
 * @return VariableInfo
 */
function parseVariable($variable) : VariableInfo
{
    return new VariableInfo($variable);
}

/**
 * Like the native PHP function <code>parse_url</code>,
 * but with a friendly API and some enhancements and fixes 
 * for a few things that the native function handles poorly.
 * 
 * @param string $url The URL to parse.
 * @return URLInfo
 */
function parseURL(string $url) : URLInfo
{
    return new URLInfo($url);
}

/**
 * Creates a throwable info instance for the specified error,
 * which enables accessing additional information on it,
 * as well as serializing it to be able to persist it in storage.
 * 
 * @param Throwable $e
 * @return ConvertHelper_ThrowableInfo
 */
function parseThrowable(Throwable $e) : ConvertHelper_ThrowableInfo
{
    return ConvertHelper_ThrowableInfo::fromThrowable($e);
}

/**
 * Restores a throwable info instance from a previously 
 * serialized array.
 * 
 * @param array $serialized
 * @return ConvertHelper_ThrowableInfo
 */
function restoreThrowable(array $serialized) : ConvertHelper_ThrowableInfo
{
    return ConvertHelper_ThrowableInfo::fromSerialized($serialized);
}

/**
 * Creates an interval wrapper, that makes it a lot easier
 * to work with date intervals. It also solves
 *
 * @param DateInterval $interval
 * @return ConvertHelper_DateInterval
 */
function parseInterval(DateInterval $interval) : ConvertHelper_DateInterval
{
    return ConvertHelper_DateInterval::fromInterval($interval);
}

/**
 * Translation function used to translate some of the internal
 * strings: if the localization is installed, it will use this
 * to do the translation.
 * 
 * @return string
 */
function t() : string
{
    $args = func_get_args();
    
    // is the localization package installed?
    if(function_exists('\AppLocalize\t'))
    {
        return call_user_func_array('\AppLocalize\t', $args);
    }
    
    // simulate the translation function
    return strval(call_user_func_array('sprintf', $args));
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
 * Creates a new StringBuilder instance.
 * 
 * @return StringBuilder
 */
function sb() : StringBuilder
{
    return new StringBuilder();
}

/**
 * Whether the current request is run via the command line.
 * @return bool
 */
function isCLI() : bool
{
    return php_sapi_name() === "cli";
}

/**
 * Initializes the utilities: this is called automatically
 * because this file is included in the files list in the
 * composer.json, guaranteeing it is always loaded.
 */
function init()
{
    if(!class_exists('\AppLocalize\Localization')) {
        return;
    }
    
    $installFolder = realpath(__DIR__.'/../');
    
    // Register the classes as a localization source,
    // so they can be found, and use the bundled localization
    // files.
    \AppLocalize\Localization::addSourceFolder(
        'application-utils',
        'Application Utils Package',
        'Composer Packages',
        $installFolder.'/localization',
        $installFolder.'/src'
    );
}

init();
