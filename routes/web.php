<?php 
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */

/**
 * Register Before Middleware
 * A global middleware that run before each request
 * If you return STATUS_ERROR the operation will be terminated else STATUS_OK
 * 
 * @example $router->middleware('GET|POST', '/*.', 'YourMiddleware::security');
*/

/**
 * Register router main page view
*/
$router->get('/', 'Welcome::home');