<?php
use \Luminova\Routing\Router;
/** @var \Luminova\Routing\Router $router */
/** @var \App\Application $app */

$router->group('demo', static function(Router $router){
   $router->command('test', 'DemoCommand::test');
   $router->command('hello', 'DemoCommand::hello');
});
