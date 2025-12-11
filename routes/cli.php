<?php
declare(strict_types=1);
/**
 * This file handles all CLI commands (e.g, `php index.php demo hello'`).
 * 
 * The following global variables are available in the current file:
 * 
 * @var \Luminova\Foundation\Core\Application $app The application object.
 * @var string $context The name of the current routing context (this file's context).
 */
use \Luminova\Routing\Router;

Router::group('demo', static function(): void {
   Router::command('hello', 'DemoController::hello');
});