<?php 
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */

$router->get('/', 'RequestController::info');