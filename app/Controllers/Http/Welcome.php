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
use \App\Errors\Controllers\ErrorController;

/**
 * This class demonstrates how HTTP web pages controller can be implemented in Luminova.
 */
#[Prefix(pattern: '/(?!api).*', onError: [ErrorController::class, 'onApiError'])]
class Welcome extends BaseController 
{
    /**
     * Index page controller method.
     * 
     * @return int Return response status code STATUS_SUCCESS or STATUS_ERROR.
     * @see /routes/web.php - The web context for method-based routing.
     */
    #[Route('/', methods: ['GET'])]
    public function home(): int
    {
        return $this->view('index');
    }
}