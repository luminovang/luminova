<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
use \Luminova\Errors\Error;

require_once __DIR__ . '/../system/Errors/Error.php';

/**
 * Initialize framework error handler
*/
Error::initialize('http');

/**
 * Autoload composer bootstrap
*/
require_once __DIR__ . '/../system/plugins/autoload.php';