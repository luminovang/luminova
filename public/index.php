<?php
/**
 * Luminova Framework front controller.
 *
 * This is the default entry point for HTTP/CLI-routable requests. It loads the framework
 * bootstrap, prepares the application working directory, and runs the route
 * context for the current request.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
declare(strict_types=1);

use Luminova\Boot;

require_once dirname(__DIR__) . '/system/Boot.php';

/**
 * Set the application document root as the current working directory.
 *
 * This is required when the application is started from the CLI or another
 * working directory.
 */
if (getcwd() . DIRECTORY_SEPARATOR !== DOCUMENT_ROOT) {
    chdir(DOCUMENT_ROOT);
}

/**
 * Register and run the route context for the current request.
 *
 * Contexts allow the application to define separate routing configurations,
 * such as `WEB`, `API`, `CONSOLE`, or `CLI`. The context router determines
 * which configuration applies to the current request and only loads the
 * required route context.
 *
 * @example - Contexts can be registered using route attributes:
 *
 * ```php
 * Boot::http()->router->context()->run();
 * ```
 *
 * @example - Or explicitly using {@see \Luminova\Routing\Prefix} instances:
 *
 * ```php
 * use Luminova\Routing\Prefix;
 * use App\Errors\Controllers\AppError;
 *
 * Boot::http()->router->context([
 *     Prefix::with(Prefix::WEB, [AppError::class, 'onTrigger']),
 *     Prefix::with(Prefix::API, [AppError::class, 'onTrigger']),
 *     Prefix::with(Prefix::CLI)
 * ])->run();
 * ```
 *
 * An error handler may be defined as a callable or a controller callback:
 *
 * ```php
 * fn(int $status, Segments $segments, Application $app): int => AppError::onTrigger($app);
 *
 * [AppError::class, 'onTrigger'];
 * ```
 *
 * @see /samples/
 * @see https://luminova.ng/docs/0.0.0/boot/public
 */
Boot::http()->router->context()->run();