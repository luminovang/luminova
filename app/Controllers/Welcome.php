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
    /**
     * Index page controller method.
     * @see /routes/web.php
     * 
     * @return int Status code.
    */
    public function home(): int
    {
        return $this->view('index');
    }

    /**
     * Json info controller method.
     * @see /routes/api.php
     * 
     * @return int Status code.
    */
    public function info(): int
    {
        return response(200)->json([
            "welcome" => [
                "status" => "OK",
                "code" => 200,
                "message" => 'Hello world!'
            ]
        ]);
    }
}
