<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
require __DIR__ . '/../bootstrap/app.php';

use \Luminova\Routing\Context;
use \Luminova\Routing\Router;
use \App\Controllers\Errors\ViewErrors;

/**
 * Ensure that we are in front controller while running script in cli mode
*/
if (getcwd() . DIRECTORY_SEPARATOR !== FRONT_CONTROLLER) {
    chdir(FRONT_CONTROLLER);
}

/**
 * Load application route context.
 * We register all our application contexts `WEB, API, CONSOLE and CLI` depending on our requirements.
 * 
 * @param Context ...$callbacks Routes context instance to each routing
 * 
 * @example Context params
 *  - @param string $name Rout name any url that starts with $name will be routed to name.php in routes/name.php
 *  - @param Closure|string|<int,string> $onError Error Handler 
 *          - `Closure` Pass a callable object or string
 *          - `array` Pass an array with your ViewErrors class name and method name to handle error.
 * 
 * @return Router Return router instance.
*/
$router = app()->router->context(
    new Context(Context::WEB, [ViewErrors::class, 'onWebError']),
    new Context(Context::API, [ViewErrors::class, 'onApiError']),
    new Context(Context::CLI)
);

/**
 * Finally run our application router instance to register our routes 
*/
$router->run();