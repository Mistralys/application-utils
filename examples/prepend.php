<?php
/**
 * Environment configuration for the example scripts.
 *
 * @package Application Utils
 * @subpackage Examples
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

    $root = __DIR__;
    
    $autoload = realpath($root.'/../vendor/autoload.php');
    
    // we need the autoloader to be present
    if($autoload === false) 
    {
        die('<b>ERROR:</b> Autoloader not present. Run composer update first.');
    }
    
   /**
    * The composer autoloader
    */
    require_once $autoload;

   /**
    * Translation global function.
    * @return string
    */
    function t()
    {
        return call_user_func_array('\AppLocalize\t', func_get_args());
    }
    
   /**
    * Translation global function.
    * @return string
    */
    function pt()
    {
        return call_user_func_array('\AppLocalize\pt', func_get_args());
    }
    
   /**
    * Translation global function.
    * @return string
    */
    function pts()
    {
        return call_user_func_array('\AppLocalize\pts', func_get_args());
    }
