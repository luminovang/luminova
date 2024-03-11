<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/

require_once __DIR__ . '/../system/plugins/autoload.php';

/**
 * Don't display errors on page 
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Check php requirements 
 * 
 * @throws trigger_error
*/
if (version_compare(PHP_VERSION, 8.0, '<')) {
    $err = 'Your PHP version must be 8.0 or higher to run PHP Luminova framework. Current version: %s' . PHP_VERSION;
    if (!ini_get('display_errors')) {
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDERR, $err);
        } elseif (!headers_sent()) {
            echo $err;
        }
    }
    trigger_error($err, E_USER_ERROR);
    exit(1);
}

/**
 * @var int STATUS_OK success status code
*/
defined('STATUS_OK') || define('STATUS_OK', 0);

/**
 * @var int STATUS_ERROR error status code
*/
defined('STATUS_ERROR') || define('STATUS_ERROR', 1);

/**
 * @var string ENVIRONMENT application development state
*/
defined('ENVIRONMENT') || define('ENVIRONMENT', env('app.environment.mood', 'development'));

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

/**
 * Set the custom error handler for non-fatal errors
 * @method Error handler
*/
set_error_handler(['\Luminova\Errors\Error', 'handle']);

/**
 * Register shutdown function to catch fatal errors
 * @method Error shutdown
*/
register_shutdown_function(['\Luminova\Errors\Error', 'shutdown']);

/**
 * Developer application Global.php file .
 * 
 * @var string $__global_dev
*/
$__global_dev = __DIR__ . '/../app/Controllers/Utils/Global.php';

/**
 * Require developer application Global.php file if exists.
*/
if(file_exists($__global_dev)){
    require_once $__global_dev;
}