<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

namespace App\Config;

use \Luminova\Core\CoreServices;
use \Luminova\Exceptions\RuntimeException;

class Services extends CoreServices
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
        // Registration Example: static::newService(\Foo\Bar\Example::class);
        // Now you can use it anywhere in application.
        // Usage Example: $example = service('Example');
    }
}