<?php 
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */

/**
 * Open in browser `http://localhost/your-project/public`
*/
$router->get('/', 'Welcome::home');