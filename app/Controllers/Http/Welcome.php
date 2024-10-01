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

use \Luminova\Base\BaseViewController;
use \Luminova\Attributes\Prefix;
use \Luminova\Attributes\Route;
use \App\Controllers\Errors\ViewErrors;

/**
 * This class demonstrates how HTTP web pages controller can be implemented in Luminova.
*/
#[Prefix(pattern: '/(?!api).*', onError: [ViewErrors::class, 'onApiError'])]
class Welcome extends BaseViewController 
{
    /**
     * Index page controller method.
     * 
     * @return int Return status code STATUS_SUCCESS or STATUS_ERROR.
     * @see /routes/web.php - The web context for method-based routing.
     */
    #[Route('/', methods: ['GET'])]
    public function home(): int
    {
        return $this->view('index');
    }
}