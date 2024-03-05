<?php 
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */


$router->get('/', 'PageController::index');

$router->bind('/docs', function() use($router) {
    $router->get('/', 'PageController::docs');
    $router->get('/([0-9-.]+)', 'PageController::docs');
    $router->get('/([0-9-.]+)/([a-zA-Z0-9-]+)', 'PageController::doc');
});
