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
 * Initialize error handling ini_set
*/
\Luminova\Errors\Error::initialize('web');