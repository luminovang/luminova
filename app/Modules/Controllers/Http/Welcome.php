<?php 
/**
 * Luminova Framework HMVC Controller Example.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Modules\Controllers\Http;

use \Luminova\Base\BaseController;
use \Luminova\Attributes\Prefix;
use \Luminova\Attributes\Route;
use \App\Errors\Controllers\ErrorController;

/**
 * Demonstrates a root module controller for serving HTTP web pages using Luminova's HMVC architecture.
 */
#[Prefix(pattern: '/.*', onError: [ErrorController::class, 'onApiError'])]
class Welcome extends BaseController
{
    /**
     * Handles the root homepage route.
     *
     * @return int Return one of STATUS_SUCCESS, STATUS_ERROR, or STATUS_SILENCE.
     * @see /routes/web.php for method-based routing configuration.
     */
    #[Route('/', methods: ['GET'])]
    public function home(): int
    {
        return $this->view('index');
    }
}