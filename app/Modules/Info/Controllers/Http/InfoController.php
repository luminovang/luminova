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

use \Luminova\Base\BaseController;
use \Luminova\Attributes\Prefix;
use \Luminova\Attributes\Route;
use \App\Errors\Controllers\ErrorController;

/**
 * Demonstrates a custom module controller for serving HTTP web pages using Luminova's HMVC architecture.
 * 
 * @see /app/Application.php - For module controller namespace registration.
 */
#[Prefix(pattern: '/info/(:root)', onError: [ErrorController::class, 'onApiError'])]
class InfoController extends BaseController
{
    /**
     * Register the controller model name
     * for non-root model.
     * 
     * @return void 
     */
    protected function onCreate(): void 
    {
        // Same as the directory name (app/Modules/Info) use `Infos`
        $this->app->setModule('Info');
    }

    /**
     * Handles the info homepage route.
     *
     * @return int Return one of STATUS_SUCCESS, STATUS_ERROR, or STATUS_SILENCE.
     */
    #[Route('/info', methods: ['GET'])]
    public function infoHome(): int
    {
        return $this->view('info');
    }
}