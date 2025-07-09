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

use \Luminova\Base\Controller;
use \Luminova\Attributes\Route;
use \Luminova\Attributes\Prefix;
use \App\Errors\Controllers\ErrorController;
use function \Luminova\Funcs\response;

/**
 * Example: Default Luminova MVC API controller.
 *
 * Demonstrates how to implement a REST API endpoint 
 * using Luminova's standard MVC (non-HMVC) structure.
 */
#[Prefix(pattern: '/api/(:root)', onError: [ErrorController::class, 'onApiError'])]
class RestController extends Controller 
{
    /**
     * Handles the "/api/info" endpoint.
     * 
     * @return int Return one of STATUS_SUCCESS, STATUS_ERROR, or STATUS_SILENCE.
     * @see /routes/api.php For method-based routing configuration.
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
