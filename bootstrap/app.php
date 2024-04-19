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
 * Initialize error handling ini_set
*/
\Luminova\Errors\Error::initialize('http');