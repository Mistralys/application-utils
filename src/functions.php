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
 * Like the native PHP function <code>parse_url</code>,
 * but with a friendly API and some enhancements and fixes 
 * for a few things that the native function handles poorly.
 * 
 * @param string $url The URL to parse.
 * @return \AppUtils\URLInfo
 */
function parseURL(string $url) : URLInfo
{
    return new URLInfo($url);
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
