<?php 
/**
 * Luminova Framework APIs Error response.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use Luminova\Luminova;
use function Luminova\Funcs\response;
use Luminova\Foundation\Error\Message;

/**
 * @var Message|null $error
 */

$isProduction = defined('PRODUCTION') && PRODUCTION === true;

$body = [
    'code'    => 0,
    'title'   => null,
    'message' => 'Something went wrong, please check your server error logs for more details.',
];

if (!$isProduction && $error instanceof Message) {
    $escape = static fn(string $value): string =>
        htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

    $body = [
        'code'    => $error->getCode(),
        'title'   => $escape($error->getName()),
        'message' => $escape($error->getDescription()),
        'file'    => $escape($error->getFile()),
        'line'    => $error->getLine(),
    ];
}

$response = [
    'error' => $body,
];

if (!$isProduction) {
    $response['framework'] = [
        'php_version' => PHP_VERSION,
        'version'     => defined(Luminova::class . '::VERSION')
            ? Luminova::VERSION
            : null,
        'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'Unknown',
    ];
}

response(500)->json($response);