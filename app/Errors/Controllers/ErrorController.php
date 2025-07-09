<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Errors\Controllers;

use \App\Application;
use \Luminova\Time\Time;
use \Luminova\Interface\RoutableInterface;
use \Luminova\Interface\ErrorHandlerInterface;
use function \Luminova\Funcs\response;

class ErrorController implements RoutableInterface, ErrorHandlerInterface
{
    /**
     * {@inheritDoc} 
     * 
     * @example - Usage:
     * 
     * ```php
     * // app/Controllers/Http/FooController.php
     * // app/Modules/<?module>/Controllers/Http/FooController.php
     * 
     * $this->app->router->trigger(500);
     * ```
     * 
     * > This is the global fallback handler for manually triggered errors.
     * > Renders an error view with the provided status code and request data.
     */
    public static function onTrigger(Application $app, int $status = 404, array $arguments = []): int
    {
        // Manually handle error based on status code here
        $template = match($status) {
            500 => '5xx',
            default => '4xx'
        };

        return $app->view->view($template)->render(
            ['data' => $arguments], 
            $status
        );
    }
  
    /**
     * Handle web-based routing errors.
     * 
     * Renders a default 404 HTML page for browser clients.
     * 
     * @param Application $app Application instance.
     * 
     * @return int Return response status code.
     */
    public static function onWebError(Application $app): int 
    {
        return $app->view->view('4xx')->render(status: 404);
    }

    /**
     * Handle API routing errors.
     * 
     * Returns a structured JSON error response for API clients.
     * 
     * @param Application $app Application instance.
     * 
     * @return int Return response status code.
     */
    public static function onApiError(Application $app): int 
    {
        $view = $app->getUri();

        return response(404)->json([
            'error' => [
                'code'      => 404,
                'view'      => $view,
                'message'   => "The endpoint [{$view}] you are trying to access does not exist.",
                'timestamp' => Time::datetime()
            ]
        ]);
    }
}