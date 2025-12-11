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
use \App\Errors\Controllers\AppError;

/**
 * Example: Luminova HMVC root-scope controller.
 *
 * Handles web routes that do not belong to a named HMVC module.
 *
 * In HMVC mode, controllers under `App\Modules\Controllers\Http` are part
 * of the root scope and handle routes without a custom module name.
 */
#[Prefix(pattern: '/(:base)', onError: [AppError::class, 'onWebError'])]
class MainController extends Controller
{
    /**
     * Handle the root (`/`) route.
     *
     * @return int One of `STATUS_SUCCESS`, `STATUS_ERROR`, or `STATUS_SILENCE`.
     *
     * @see /routes/web.php For method-based routing configuration.
     */
    #[Route('/', methods: ['GET'])]
    public function home(): int
    {
        return $this->view('index', [
            'title' => 'Welcome to PHP Luminova',
        ]);
    }

    /**
     * Handle the `/about` route.
     *
     * @return int Status code `STATUS_SUCCESS`
     */
    #[Route('/about', methods: ['GET'])]
    public function about(): int
    {
        return $this->send(
            '<main>
                <h1>About Us</h1>
                <p>Learn more about our company, team, and mission.</p>
            </main>',
            ['Content-Type' => 'text/html'],
        );
    }
}