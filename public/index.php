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

/**
 * Grab our application singleton instance 
 * 
 * @var Application $app
*/
$app = Application::getInstance();

/**
 * Define our public application front controller of not defined 
 * 
 * @var string PUBLIC_CONTROLLER
*/
defined('PUBLIC_CONTROLLER') || define('PUBLIC_CONTROLLER', __DIR__ . DIRECTORY_SEPARATOR);

/**
 * Ensure that we are in front controller 
 * While running script in cli mode
*/
if (getcwd() . DIRECTORY_SEPARATOR !== PUBLIC_CONTROLLER) {
    chdir(PUBLIC_CONTROLLER);
}

/**
 * Define a function for the web error handler
 * 
 * @var string $web_error
 * @global Application $app make our application instance available
 * 
 * @return void 
*/
$web_error = function () use ($app): void {
    $app->view("404")->render([
        "error_view" => $app->getView()
    ]);
};
 

/**
 * Define a function for the API error handler
 * 
 * @var string $api_error
 * @global Application $app make our application instance available
 * 
 * @return void 
*/
$api_error = function () use($app): void {
    header("HTTP/1.0 404 Not Found");
    header("Content-type: application/json");
    exit(json_encode([
        "error" => [
            "code" => 404,
            "details" => "The endpoint [" . $app->getView() . "] you are trying to access does not exist.",
            "timestamp" => date("Y-m-d H:i:s")
        ]
    ]));
};

/**
 * Bootstraps Load The Application Context
 * We register all our application contexts `WEB, API, CONSOLE and CLI` depending on our requirements 
 * Bootstraps the router and set the error handler based on context
 * 
 * @param BaseApplication $app Application Instance
 * @param Bootstrap ...$callbacks The Bootstrap instance to each routing
 * 
 * @example Bootstrap params
 *  - @param string $name Rout name any url that starts with $name will be routed to name.php in routes/name.php
 *  - @param ?callable $onError Error Handler 
*/
$app->router->bootstraps($app,
    new Bootstrap(Bootstrap::WEB, $web_error),
    new Bootstrap(Bootstrap::API, $api_error),
    new Bootstrap(Bootstrap::CLI)
);

/**
 * Finally run our application router instance to register our routes 
*/
$app->router->run();