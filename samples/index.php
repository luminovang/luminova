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
 * Register all our application contexts such as `WEB, API, CONSOLE and CLI` depending on our requirements.
 * 
 * @param Context|array<string,mixed> $contexts [, Context|array<string,mixed> ...$] Routes context method accept arguments array or new Context class instance.
 * 
 * @example For context class instance, pass new instance of Context as shown below.
 *  - @param string $name Route URI prefix name, any url that starts with $name will be routed to name.php in routes/name.php.
 *  - @param Closure|array<int,string>|null $onError For error argument, which handles 404 errors.
 *          - `Closure` Using closures, pass a callable object or string as shown below.
 *              fn(Application $app): int => ViewErrors::onWebError($app))
 *          - `Array` For array, pass a list array with only 2 elements, your error controller string-class name and the method name to handle error.
 *              [ErrorHandlerController::class, 'handlerMethod']
 * 
 * 
 * @example For array context, pass an array of Context configuration expecting only associative array with 2 keys `prefix` and `error`.
 *      -   The key `prefix` Should indicate your context name.
 *      -   The key `error` Should indicate your error handler, which can be `NULL`, `Closure` or `Array` indicating controller class and method.
 *              [
 *                   'prefix' => 'foo', 
 *                   'error' => [ViewErrors::class, 'onFooError'] 
 *               ]
 * 
 * Finally run our application router instance to register our routes.
*/
Boot::http()->router->context(
    new Context(Context::WEB, [ViewErrors::class, 'onWebError']),
    new Context(Context::API, [ViewErrors::class, 'onApiError']),
    new Context(Context::CLI)
)->run();