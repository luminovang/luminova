<?php 
//use Luminova\Command\Terminal;
/** @var \Luminova\Routing\Router $router */
/** @var \App\Controllers\Application $app */

/**
* Register before middleware
* A global middleware that run before and after each command is executed
*    $router->authenticate(function(){
*        return Terminal::STATUS_OK;
*    });
*
* @example $router->command("command", 'Command::run');
* @example $router->command("test", 'Command::test');
*/

