<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use \Luminova\Time\Time;

response(500, true)->json([
    'error' => [
        'code' => $stack?->getCode(),
        'title' => escape($name),
        'message' => escape($stack?->getMessage()),
        'timestamp' => Time::datetime(),
    ],
    'framework' => [
        'php_version' => PHP_VERSION,
        'version' => app()::VERSION,
        'environment' => ENVIRONMENT,
    ]
]);