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
use \Luminova\Routing\Prefix;
use \App\Errors\Controllers\ErrorController;

require_once __DIR__ . '/../system/Boot.php';

/**
 * Ensure that we are in front controller while running script in cli mode
*/
if (getcwd() . DIRECTORY_SEPARATOR !== DOCUMENT_ROOT) {
    chdir(DOCUMENT_ROOT);
}

/**
 * Load application route context.
 * Register all your application contexts such as `WEB, API, CONSOLE and CLI` depending on our requirements.
 * 
 * @param Prefix|array<string,mixed> $contexts [, Prefix|array<string,mixed> ...$] Routes context method accept arguments array or new Prefix class instance.
 * 
 * @example For context class instance, pass new instance of Context as shown below.
 *  - @param string $name Route URI prefix name, any url that starts with $name will be routed to name.php in routes/name.php.
 *  - @param Closure|array<int,string>|null $onError For error argument, which handles 404 errors.
 *          - `Closure` Using closures, pass a callable object or string as shown below.
 *              fn(Application $app): int => ErrorController::onWebError($app))
 *          - `Array` For array, pass a list array with only 2 elements, your error controller string-class name and the method name to handle error.
 *              [ErrorHandlerController::class, 'handlerMethod']
 * 
 * 
 * @example For array context, pass an array of prefix configuration expecting only associative array with 2 keys `prefix` and `error`.
 *      -   The key `prefix` Should indicate your context name.
 *      -   The key `error` Should indicate your error handler, which can be `NULL`, `Closure` or `Array` indicating controller class and method.
 *              [
 *                   'prefix' => 'foo', 
 *                   'error' => [ErrorController::class, 'onFooError'] 
 *               ]
 * 
 * Finally run your application router to register and boot only request context for optimal performance.
 */
Boot::http()->router->context(
    new Prefix(Prefix::WEB, [ErrorController::class, 'onWebError']),
    new Prefix(Prefix::API, [ErrorController::class, 'onApiError']),
    new Prefix(Prefix::CLI)
)->run();