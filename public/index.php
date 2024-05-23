<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
declare(strict_types=1);

use \Luminova\Boot;
use \Luminova\Routing\Context;
use \App\Controllers\Errors\ViewErrors;

require_once __DIR__ . '/../system/Boot.php';

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
 * @param Context ...$contexts Routes context instance to each routing.
 * 
 * @example Context params
 *  - @param string $name Route name, any url that starts with $name will be routed to name.php in routes/name.php.
 *  - @param Closure|array<int,string>|null $onError 404 error handler.
 *          - `Closure` Pass a callable object or string
 *              fn(Application $app): int => ViewErrors::onWebError($app))
 *          - `array` Pass an array with only 2 elements, your ViewErrors class name and method name to handle error.
 * 
 * Finally run our application router instance to register our routes 
*/
Boot::http()->router->context(
    new Context(Context::WEB, [ViewErrors::class, 'onWebError']),
    new Context(Context::API, [ViewErrors::class, 'onApiError']),
    new Context(Context::CLI)
)->run();