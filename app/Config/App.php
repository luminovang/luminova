<?php 
/**
 * Luminova Framework Application general configuration
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
namespace App\Config;

use \Luminova\Base\Configuration;

final class App extends Configuration
{
    /**
     * Example us case.
     * 
     * @var string PHONE
     * 
     * App\Config\App::PHONE;
     */
    public const PHONE = '123456789';

    /**
     * On create overrides.
     *
     * @return void
     */
    protected function onCreate(): void 
    {}
}