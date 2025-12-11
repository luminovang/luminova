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
use \Luminova\Attributes\Route;
use \Luminova\Attributes\Prefix;
use \App\Errors\Controllers\AppError;

/**
 * Example: Luminova HMVC module controller.
 *
 * Demonstrates serving HTTP pages using Luminova's HMVC architecture.
 *
 * The controller belongs to the `Info` module based on its namespace:
 * `App\Modules\Info\Controllers\Http`.
 *
 * @see /app/Application.php For module namespace registration.
 */
#[Prefix(pattern: '/info/(:root)', onError: [AppError::class, 'onWebError'])]
class MainInfoController extends Controller
{
    /**
     * {@inheritDoc}
     *
     * Called when the controller is created.
     *
     * The HMVC module is normally resolved automatically from the controller
     * namespace. Use `setModule()` only when the module name must be explicitly
     * configured or overridden.
     */
    protected function onCreate(): void
    {
        // Optional: explicitly set the HMVC module.
        // $this->tpl->setModule('Info');
    }

    /**
     * Handle the `/info` route.
     *
     * @return int One of `STATUS_SUCCESS`, `STATUS_ERROR`, or `STATUS_SILENCE`.
     */
    #[Route('/info', methods: ['GET'])]
    public function info(): int
    {
        return $this->view('info');
    }
}