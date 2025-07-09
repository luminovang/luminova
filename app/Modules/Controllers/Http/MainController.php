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

use \Luminova\Base\Controller;
use \Luminova\Attributes\Route;
use \Luminova\Attributes\Prefix;
use \App\Errors\Controllers\ErrorController;

/**
 * Example: Luminova HMVC root controller.
 *
 * Serves as the main module controller for handling 
 * the root ("/") web page in Luminova's HMVC architecture.
 */
#[Prefix(pattern: '/(:base)', onError: [ErrorController::class, 'onWebError'])]
class MainController extends Controller
{
    /**
     * Handles the root homepage route ("/").
     *
     * @return int Return one of STATUS_SUCCESS, STATUS_ERROR, or STATUS_SILENCE.
     * @see /routes/web.php For method-based routing configuration.
     */
    #[Route('/', methods: ['GET'])]
    public function home(): int
    {
        return $this->view('index', [
            'title' => 'Welcome to PHP Luminova'
        ]);
    }
}