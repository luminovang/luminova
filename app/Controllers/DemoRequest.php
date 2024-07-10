<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers;

use \Luminova\Base\BaseController;

/**
 * This class demonstrates how web pages or API controller can be implemented using PHP Luminova.
*/
class DemoRequest extends BaseController 
{
    /**
     * This is an example of API controller.
     * 
     * @see /routes/api.php - The api context.
     * 
     * @return int Return status code STATUS_SUCCESS or STATUS_ERROR.
    */
    public function info(): int
    {
        return response(200)->json([
            "welcome" => [
                "status" => "OK",
                "code" => 200,
                "message" => 'Hello world!'
            ]
        ]);
    }
}
