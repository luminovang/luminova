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

use Luminova\Base\BaseViewController;
use Luminova\Base\BaseConfig;

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
                "version" => BaseConfig::version(),
                "framework" => BaseConfig::copyright(),
                "timestamp" => date("Y-m-d H:i:s")
            ]
        ]);
    }
    
}
