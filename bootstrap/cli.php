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
 * And display errors to developers when using it from the CLI.
*/
ini_set('display_errors', '1');
error_reporting(E_ALL);

/**
 * Refuse to run when called from php-cgi
*/
if (strpos(PHP_SAPI, 'cgi') === 0) {
    echo "The cli tool is not supported when running php-cgi. It needs php-cli to function!";
    exit(1);
}

/**
 * Load basic requirements.
*/
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/functions.php';
require_once APP_ROOT . 'system/Errors/ErrorStack.php';
require_once APP_ROOT . 'system/Application/Foundation.php';

/**
 * Set default timezone
*/
date_default_timezone_set(env("app.timezone", 'UTC'));

/**
 * Limits the maximum execution time
*/
set_time_limit((int) env("script.execution.limit", 30));

/**
 * Set whether a client disconnect should abort script execution
*/
ignore_user_abort((bool) env('script.ignore.abort', true));

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
 * Now we can autoload composer bootstrap.
*/
require_once APP_ROOT . 'system/plugins/autoload.php';
require_once __DIR__ . '/features.php';

/**
 * @var string IS_UP no composer error everything is okay for now.
*/
defined('IS_UP') || define('IS_UP', true);