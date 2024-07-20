<?php
use \Luminova\Routing\Router;
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */

$router->group('demo', static function(Router $router){
   $router->command('hello', 'DemoCommand::hello');
});
