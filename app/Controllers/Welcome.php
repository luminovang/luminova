<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers;

use \Luminova\Base\BaseViewController;
/**
 * This class demonstrates how web pages controller can be implemented using PHP Luminova.
*/
class Welcome extends BaseViewController 
{
    /**
     * Index page controller method.
     * @see /routes/web.php - The web context.
     * 
     * @return int Return status code STATUS_SUCCESS or STATUS_ERROR.
    */
    public function home(): int
    {
        return $this->view('index');
    }
}