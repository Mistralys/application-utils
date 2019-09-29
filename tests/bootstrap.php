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
    
