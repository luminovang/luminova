<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Http;

use \Luminova\Base\BaseController;
use \Luminova\Attributes\Prefix;
use \Luminova\Attributes\Route;
use \App\Controllers\Errors\ViewErrors;

/**
 * This class demonstrates how HTTP web pages or REST API controller can be implemented in Luminova.
*/
#[Prefix(pattern: '/api/(:root)', onError: [ViewErrors::class, 'onApiError'])]
class RestController extends BaseController 
{
    /**
     * This is an example of API controller.
     * 
     * @return int Return response status code STATUS_SUCCESS or STATUS_ERROR.
     * 
     * @see /routes/api.php - The api context for method-based routing.
     */
    #[Route('/api/info', methods: ['POST'])]
    public function info(): int
    {
        return response(200)->json([
            'welcome' => [
                'status' => 'OK',
                'code' => 200,
                'message' => 'Hello world!'
            ]
        ]);
    }
}
