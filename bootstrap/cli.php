<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/

/**
 * We want errors to be shown when using it from the CLI.
 * And display errors to developers 
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Autoload composers 
*/
require_once __DIR__ . '/../system/plugins/autoload.php';

/**
 * Check php requirements 
 * 
 * @throws STDERR
*/
if (version_compare(PHP_VERSION, 8.0, '<')) {
    $err = 'Your PHP version must be 8.0 or higher to run PHP Luminova framework. Current version: %s' . PHP_VERSION;

    if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
        fwrite(STDERR, $err);
    }
    exit($err);
}

/**
 * Refuse to run when called from php-cgi
 * 
 * @throws STDERR
*/
if (strpos(PHP_SAPI, 'cgi') === 0) {
    $err = "The cli tool is not supported when running php-cgi. It needs php-cli to function!\n\n";

    fwrite(STDERR, $err);
    exit($err);
}

/**
 * @var string CLI_ENVIRONMENT application cli development state
*/
defined('CLI_ENVIRONMENT') || define('CLI_ENVIRONMENT', env('cli.environment.mood', 'testing'));

/**
 * @var string STDOUT if it's not already defined
*/
defined('STDOUT') || define('STDOUT', 'php://output');

/**
 * @var string STDIN if it's not already defined
*/
defined('STDIN') || define('STDIN', 'php://stdin');

/**
 * @var string STDERR if it's not already defined
*/
defined('STDERR') || define('STDERR', 'php://stderr');

/**
 * @var string HOME_PATH home directory path 
*/
defined('HOME_PATH') || define('HOME_PATH', realpath(rtrim(getcwd(), '\\/ ')) . DIRECTORY_SEPARATOR);

/**
 * @var string PUBLIC_PATH Public directory path 
*/
defined('PUBLIC_PATH') || define('PUBLIC_PATH', realpath(HOME_PATH . 'public') . DIRECTORY_SEPARATOR);

/**
 * @var string FRONT_CONTROLLER Front controller path
*/
defined('FRONT_CONTROLLER') || define('FRONT_CONTROLLER', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);