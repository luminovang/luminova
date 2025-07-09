<?php
declare(strict_types=1);

use \Luminova\Routing\Router;
/**
 * This file handles all CLI commands (e.g, `php index.php demo hello'`).
 * 
 * The following global variables are available in the current file:
 * 
 * @var \Luminova\Routing\Router $router The routing object.
 * @var \Luminova\Foundation\Core\Application $app The application object.
 * @var string $context The name of the current routing context (this file's context).
 */

Router::group('demo', static function(): void {
   Router::command('hello', 'CommandController::hello');
});