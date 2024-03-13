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
 * Initialize services
*/
if(is_feature('feature.app.services', true)){
    factory('initializeServices');
}

/**
 * Anonymous function to register class configuration
 * 
 * @return void 
*/

(function(): void {
    if(is_feature('feature.app.class.aliases', false)){
        $modules = path('controllers') . 'Config' . DIRECTORY_SEPARATOR . 'Modules.php';
        if(file_exists($modules)){
            $config = require_once $modules;

            if(isset($config['aliases'])){
                foreach ($config['aliases'] as $alias => $namespace) {
                    if (!class_alias($namespace, $alias)) {
                        logger('warning', "Failed to create an alias [$alias] for class [$namespace]");
                    }
                }
            }
        }
    }
})();
