<?php
declare(strict_types=1);
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Errors\Controllers;

use Luminova\Time\Time;
use Luminova\Routing\Router;
use Luminova\Routing\Segments;
use Luminova\Interface\RoutableInterface;
use function Luminova\Funcs\{view, response};
use Luminova\Interface\ErrorControllerInterface;

/**
 * Default error controller for application routing errors.
 *
 * Provides fallback handlers for manually triggered, web, and API routing
 * errors. Applications may replace these handlers with their own
 * implementations.
 */
class AppError implements RoutableInterface, ErrorControllerInterface
{
    /**
     * {@inheritDoc}
     *
     * @example - Usage:
     * 
     * ```php
     * // app/Controllers/Http/FooController.php
     * // app/Modules/<Module>/Controllers/Http/FooController.php
     *
     * $this->app->router->trigger(500);
     * ```
     *
     * > This is the default fallback handler for manually triggered errors.
     */
    public static function onTrigger(
        int $status,
        Segments $segments
    ): int 
    {
        if(Router::isApiPrefix()){
            return self::errorJson($status, $segments);
        }
        
        $template = match ($status) {
            500 => '5xx',
            default => '4xx',
        };

        return view($template)->render(
            [
                'status' => $status,
                'uri'    => $segments->all()
            ],
            $status
        );
    }

    /**
     * Define a function for the API error handler.
     * 
     * @param int $status HTTP status code associated with the error.
     * @param Segments $segments Request URI segments associated with the error.
     * 
     * @return int Return response status code. 
     */
    private static function errorJson(int $status, Segments $segments): int 
    {
        $view = $segments->toString();

        return response($status)->json([
            'code'      => $status,
            'view'      => $view,
            'message'   => "The endpoint [{$view}] you are trying to access does not exist.",
            'timestamp' => Time::datetime()
        ]);
    }
}