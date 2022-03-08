<?php
/**
* Main bootstrapper used to set up the testsuites environment.
*
* @package Application Utils
* @subpackage Tests
* @author Sebastian Mordziol <s.mordziol@mistralys.eu>
*/

declare(strict_types=1);

const TESTS_ROOT = __DIR__;
const APP_UTILS_TESTSUITE = 'true';

$autoloader = __DIR__ . '/../vendor/autoload.php';

if(!file_exists($autoloader))
{
    die('ERROR: The autoloader is not present. Please run composer install first.');
}

require_once $autoloader;

$configFile = __DIR__ . '/config.php';

if(file_exists($configFile))
{
    require_once $configFile;
}

