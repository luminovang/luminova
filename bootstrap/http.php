<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
use \Luminova\Application\Foundation;

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
 * Initialize framework error handler.
*/
Foundation::initialize();

/**
 * Now we can autoload composer bootstrap.
*/
require_once APP_ROOT . 'system/plugins/autoload.php';
require_once __DIR__ . '/features.php';

/**
 * @var string IS_UP no composer error everything is okay for now.
*/
defined('IS_UP') || define('IS_UP', true);