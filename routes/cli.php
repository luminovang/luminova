<?php
use \Luminova\Routing\Router;
/**
 * This file handles all CLI commands (e.g, `php index.php demo hello'`).
 * 
 * The following global variables are available in the current file:
 * 
 * @var \Luminova\Routing\Router $router The routing instance.
 * @var \App\Application $app The application instance that provides access to the overall application context.
 * @var string $context The name of the current routing context (this file's context).
 */

$router->group('demo', static function(Router $router){
   $router->command('hello', 'CliController::hello');
});