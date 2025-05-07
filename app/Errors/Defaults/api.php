<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
$error = [
    'code' => $stack ? $stack->getCode() : 0,
    'title' => $stack ? htmlspecialchars($stack->getName()) : null,
];

if (defined('PRODUCTION') && !PRODUCTION) {
    $error['message'] = $stack ? htmlspecialchars($stack->getMessage()) : null;
}else{
    $error['message'] = 'Something went wrong, please check your server error logs for more details.';
}

response(500)->json([
    'error' => $error,
    'framework' => [
        'php_version' => PHP_VERSION,
        'version' => defined('\Luminova\Luminova::VERSION') ? \Luminova\Luminova::VERSION : '1.0.0',
        'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'Unknown',
    ]
]);