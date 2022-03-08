<?php
/**
 * Environment configuration for the example scripts.
 *
 * @package Application Utils
 * @subpackage Examples
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';

if(!file_exists($autoload))
{
    die('<b>ERROR:</b> Autoloader not present. Please run composer install first.');
}

require_once $autoload;

