<?php 
/**
 * Luminova Framework HMVC Controller Example.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Modules\Info\Controllers\Http;

use \Luminova\Base\Controller;
use \Luminova\Attributes\Prefix;
use \Luminova\Attributes\Route;
use \App\Errors\Controllers\ErrorController;

/**
 * Example: Luminova HMVC module controller.
 *
 * Demonstrates how to serve HTTP pages using Luminova's HMVC architecture.
 * 
 * @see /app/Application.php - For module namespace registration.
 */
#[Prefix(pattern: '/info/(:root)', onError: [ErrorController::class, 'onWebError'])]
class InfoController extends Controller
{
    /**
     * {@inheritDoc}
     * 
     * Called when the controller is created.
     * 
     * Registers the module name for resolving non-root view paths.
     */
    protected function onCreate(): void 
    {
        // Matches directory name (app/Modules/Info), use 'Info' not 'Infos'
        $this->app->view->setModule('Info');
    }

    /**
     * Handles the /info route.
     *
     * @return int Return one of STATUS_SUCCESS, STATUS_ERROR, or STATUS_SILENCE.
     */
    #[Route('/info', methods: ['GET'])]
    public function info(): int
    {
        return $this->view('info');
    }
}