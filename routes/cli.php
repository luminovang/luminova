<?php 
use \Luminova\Routing\Router;
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */

/**
* Register before middleware
* A global middleware that run before and after each command is executed
* @example 
*   $router->before(function(){
*        return Terminal::STATUS_OK;
*    });
*/

$router->group("blog", function(Router $router): void
{
    $router->command('list', 'BlogCommand::list');
    $router->command('/read/id/(:value)', 'BlogCommand::read');
});