<?php 
/**
 * Luminova Framework APIs Error response.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use \Luminova\Foundation\Error\Message;
use function \Luminova\Funcs\response;

/**
 * @var Message|null $error
 */
$body = [
    'code' => 0,
    'title' => null,
    'message' => 'Something went wrong, please check your server error logs for more details.'
];

if (($error instanceof Message) && defined('PRODUCTION') && !PRODUCTION) {
    $body['code'] = $error->getCode();
    $body['title'] = htmlspecialchars($error->getName());
    $body['message'] = htmlspecialchars($error->getDescription());
    $body['file'] = htmlspecialchars($error->getFile());
    $body['line'] = $error->getLine();
}

response(500)->json([
    'error' => $body,
    'framework' => [
        'php_version' => PHP_VERSION,
        'version' => defined('\Luminova\Luminova::VERSION') ? \Luminova\Luminova::VERSION : '1.0.0',
        'environment' => defined('PRODUCTION') ? ENVIRONMENT : 'Unknown',
    ]
]);