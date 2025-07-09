<?php 
/**
 * Luminova Framework APIs Error response.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use \Luminova\Errors\ErrorHandler;

$error = [
    'code' => 0,
    'title' => null,
    'message' => 'Something went wrong, please check your server error logs for more details.'
];

if (($stack instanceof ErrorHandler) && defined('PRODUCTION') && !PRODUCTION) {
    $error['code'] = $stack->getCode();
    $error['title'] = htmlspecialchars($stack->getName());
    $error['message'] = htmlspecialchars($stack->getFilteredMessage());
    $error['file'] = htmlspecialchars($stack->getFile());
    $error['line'] = $stack->getLine();
}

\Luminova\Funcs\response(500)->json([
    'error' => $error,
    'framework' => [
        'php_version' => PHP_VERSION,
        'version' => defined('\Luminova\Luminova::VERSION') ? \Luminova\Luminova::VERSION : '1.0.0',
        'environment' => defined('PRODUCTION') ? ENVIRONMENT : 'Unknown',
    ]
]);