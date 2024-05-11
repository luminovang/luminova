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

use \Luminova\Routing\Bootstrap;
use \App\Controllers\Application;
use \App\Controllers\Errors\ViewErrors;

/**
 * Ensure that we are in front controller while running script in cli mode
*/
if (getcwd() . DIRECTORY_SEPARATOR !== FRONT_CONTROLLER) {
    chdir(FRONT_CONTROLLER);
}

/**
 * Grab our application singleton instance.
 * 
 * @var Application $app
*/
$app = app();

/**
 * Bootstraps Load The Application Context
 * We register all our application contexts `WEB, API, CONSOLE and CLI` depending on our requirements 
 * Bootstraps the router and set the error handler based on context
 * 
 * @param Application $app Application Instance
 * @param Bootstrap ...$callbacks The Bootstrap instance to each routing
 * 
 * @example Bootstrap params
 *  - @param string $name Rout name any url that starts with $name will be routed to name.php in routes/name.php
 *  - @param Closure|string|<int,string> $onError Error Handler 
 *          - `Closure` Pass a callable object or string
 *          - `array` Pass an array with your ViewErrors class name and method name to handle error.
*/
$app->router->bootstraps($app,
    new Bootstrap(Bootstrap::WEB, [ViewErrors::class, 'onWebError']),
    new Bootstrap(Bootstrap::API, [ViewErrors::class, 'onApiError']),
    new Bootstrap(Bootstrap::CLI)
);

/**
 * Finally run our application router instance to register our routes 
*/
$app->router->run();