<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

\Luminova\Config\DotEnv::register(root(__DIR__, '.env'));

if(!function_exists('env')){
    /**
     * Get environment variables.
     *
     * @param string $key The key to retrieve.
     * @param mixed $default The default value to return if the key is not found.
     * 
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed 
    {
        if (getenv($key) !== false) {
            $env = getenv($key);
        }elseif (isset($_ENV[$key])) {
            $env = $_ENV[$key];
        }elseif (isset($_SERVER[$key])) {
            $env = $_SERVER[$key];
        }

        return $env ?? $default;
    }
}

if(!function_exists('locale')){
    /**
    * Set locale or return local 
    *
    * @param ?string $locale If locale is present it will set it else return default locale
    *
    * @return string|bool;
    */
    function locale(?string $locale = null): string|bool 
    {
        if($locale === null){
            $locale = env('app.locale', 'en');

            return $locale;
        }else{
            setenv('app.locale', $locale, true);
        }

        return true;
    }
}

if(!function_exists('is_feature')){
    /**
    * Check if feature is enabled in env file 
    *
    * @param string $key Feature key name
    * @param bool $default 
    *
    * @return bool
    */
    function is_feature(string $key, bool $default = false): bool 
    {
        return env($key, $default) === 'enable';
    }
}

/**
 * Set default timezone
*/
date_default_timezone_set(env("app.timezone", 'UTC'));

/**
 * @var int STATUS_OK success status code
*/
defined('STATUS_OK') || define('STATUS_OK', 0);

/**
 * @var int STATUS_ERROR error status code
*/
defined('STATUS_ERROR') || define('STATUS_ERROR', 1);

/**
 * @var string ENVIRONMENT application development state
*/
defined('ENVIRONMENT') || define('ENVIRONMENT', env('app.environment.mood', 'development'));


/**
 * Initialize services
*/
if(is_feature('feature.app.services', true)){
    factory('initializeServices');
}

/**
 * Anonymous function to register class configuration
 * @param string $path Controller path 
 * 
 * @return void 
*/

(function(string $path): void {
    if(is_feature('feature.app.class.aliases', false) && !defined('INIT_DEV_MODULES')){
        $modules = $path . 'Config' . DIRECTORY_SEPARATOR . 'Modules.php';
        if(file_exists($modules)){
            define('INIT_DEV_MODULES', true);
            $config = require $modules;

            if(isset($config['aliases'])){
                foreach ($config['aliases'] as $alias => $namespace) {
                    if (!class_alias($namespace, $alias)) {
                        logger('warning', "Failed to create an alias [$alias] for class [$namespace]");
                    }
                }
            }
        }
    }

    if(is_feature('feature.app.dev.functions', true) && !defined('INIT_DEV_FUNCTIONS')){

        $global = $path . 'Utils' . DIRECTORY_SEPARATOR . 'Global.php';

        if(file_exists($global)){
            define('INIT_DEV_FUNCTIONS', true);
            require $global;
        }
    }
})(path('controllers'));
