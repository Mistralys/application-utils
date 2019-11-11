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

    require_once TESTS_ROOT.'/../vendor/autoload.php';
    
    /**
     * Dummy test interfaces for the PHPClassInfo tests.
     */
    require_once TESTS_ROOT.'/classes/FooInterface.php';
    
    /**
     * Dummy test classes for the PHPClassInfo tests.
     */
    require_once TESTS_ROOT.'/classes/FooClass.php';