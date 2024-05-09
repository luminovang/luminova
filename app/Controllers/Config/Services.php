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

class Services extends BaseServices
{
    /**
     * Register services to autoload and discoverable in services.
     * 
     * @example static::addService(FooClass::class, "Foo Argument", true, true);
     * 
     * @param void
    */
    public static function bootstrap(): void
    {
        //static::addService(FooClass::class, "Foo Argument", true, true);
        //static::addService(BarClass::class, "Bar Argument", true, true);
    }
}