<?php
/**
 * Tests configuration file template: rename to config.php
 * to enable additional tests.
 * 
 * @package Application Utils
 * @subpackage Tests
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

   /**
    * URL that allows accessing the tests suite via a 
    * webserver: required to enable request helper tests
    * for sending files and the like.
    * 
    * @var string 
    */
	define('TESTS_WEBSERVER_URL', 'http://127.0.0.1/svn/tools/application-utils/tests/');
