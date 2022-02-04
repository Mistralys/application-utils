<?php

/**
 * Main bootstrapper used to set up the testsuites environment.
 * 
 * @package Application Utils
 * @subpackage Tests
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

    /**
     * The tests root folder (this file's location)
     * @var string
     */
    define('TESTS_ROOT', __DIR__ );

    define('APP_UTILS_TESTSUITE', 'true');

    $autoloader = realpath(TESTS_ROOT.'/../vendor/autoload.php');
    
    if($autoloader === false) 
    {
        die('ERROR: The autoloader is not present. Run composer install first.');
    }

   /**
    * The composer autoloader
    */
    require_once $autoloader;
    
    $configFile = TESTS_ROOT.'/config.php';
    
    if(file_exists($configFile))
    {
        require_once $configFile;
    }

