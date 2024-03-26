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
use \App\Controllers\Config\Config;

class Welcome extends BaseViewController 
{
    public function page(): void
    {
        $this->view('index');
    }

    public function info(): void
    {
        header("Content-type: application/json");
        echo json_encode([
            "error" => [
                "status" => "OK",
                "code" => 200,
                "version" => Config::version(),
                "framework" => Config::copyright(),
                "timestamp" => date("Y-m-d H:i:s")
            ]
        ], JSON_PRETTY_PRINT);
    }
    
}
