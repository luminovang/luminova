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

/**
 * Example: Default Luminova MVC controller.
 *
 * Demonstrates how to implement a basic HTTP page controller 
 * using Luminova's standard MVC (non-HMVC) structure.
 */
#[Prefix(pattern: '/(:base)', exclude: ['api'], onError: [ErrorController::class, 'onWebError'])]
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