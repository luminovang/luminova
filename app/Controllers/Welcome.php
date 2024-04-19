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

class Welcome extends BaseViewController 
{
    public function home(): void
    {
        $this->view('index');
    }

    public function info(): void
    {
        response(200)->json([
            "welcome" => [
                "status" => "OK",
                "code" => 200,
                "message" => 'Hello world!'
            ]
        ]);
    }
    
}
