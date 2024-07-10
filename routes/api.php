<?php 
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */

/**
 * Send a post request to `http://localhost/your-project/public/api/info`
*/
$router->post('/info', 'DemoRequest::info');