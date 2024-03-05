<?php 
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */


/**
 * Register Before Middleware
 * A global middleware that run before each request
 * If you return STATUS_ERROR the operation will be terminated else STATUS_OK
 * 
 * @example $router->before('GET|POST', '/*.', 'YourMiddleware::security');
 * @example 
 * $router->before('GET|POST', '/.*', function() use($app){
 *      if($app->session->online()){
 *          $app->view('login)->render();
 *          return STATUS_OK;
 *      } 
 *      return STATUS_OK;
 * });
 *
*/

 /**
  * Register router main page view
 */
$router->get('/', 'Welcome::page');
