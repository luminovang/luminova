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
     * Register bootstrap autoload all your application services.
     * Add each service in a new line within the bootstrap method.
     * 
     * Usage:
     *     - static::newService(Configuration::class) as $config = service('Configuration')
     *     - static::newService('\Luminova\Config\Configuration') as $config = service('Configuration')
     *     - static::newService(Configuration:class, 'config') as $config = service('config')
     *     - Services::Configuration()
     *     - Services::config()
     * 
     * @return void
     * @throws RuntimeException Throws If the service already exists causing duplicate service.
    */
    public function bootstrap(): void
    {
        //static::newService(FooClass::class, 'foo', true, true);
        //static::newService(BarClass::class, null, true, true, ['Bar Argument']);
    }
}