<?php 
use \Luminova\Routing\Router;
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */

/**
 * Open terminal window CD to public/ directory and run command `php index.php demo --help`,
 * To display help information for demo.
*/
$router->group("demo", static function(Router $router)
{
    $router->command("hello", 'DemoCommand::hello');
});
