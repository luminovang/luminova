<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

namespace App\Controllers\Config;

use \Luminova\Base\BaseServices;
use \App\Controllers\Config\ServiceTest;

/**
 * Services methods
 * 
 * @method static ServiceTest  ServiceTest(bool $shared = true, bool $serialize = false)   @return Test
 */

class Services extends BaseServices
{
    public static function bootstrap(): void
    {
        static::addService(ServiceTest::class, "Test Argument", true, true);
    }
}