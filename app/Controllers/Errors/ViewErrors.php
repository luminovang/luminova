<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

namespace App\Controllers\Errors;

use \Luminova\Base\BaseViewController;
use \Luminova\Time\Time;
use \App\Application;
use \Luminova\Interface\ErrorHandlerInterface;

class ViewErrors extends BaseViewController implements ErrorHandlerInterface
{
    /**
     * Define a function for the web error handler
     * 
     * @param Application $app Application instance available
     * 
     * @return int Return status code. 
    */
    public static function onWebError(Application $app): int 
    {
        return $app->view('404')->render();
    }

    /**
     * Define a function for the API error handler
     * 
     * @param Application $app Application instance available
     * 
     * @return int Return status code. 
    */
    public static function onApiError(Application $app): int 
    {
        return response(404, false)->json([
            'error' => [
                'code' => 404,
                'title' => $app->getView(),
                'message' => "The endpoint [" . $app->getView() . "] you are trying to access does not exist.",
                'timestamp' => Time::datetime(),
            ]
        ]);
    }
}