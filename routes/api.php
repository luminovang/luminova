<?php 
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */


/*
| Register Before Middleware
| A global middleware that run before and after each request
*/
//$router->before('GET|POST', '/*', App\Controllers\YourMiddleware::class);
 
 /**
  * Register router main page view
 */
$router->get('/', 'Welcome::info');