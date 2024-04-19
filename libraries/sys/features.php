<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/

/**
 * Autoload register psr-4 classes
*/
if(is_feature('feature.app.autoload.psr4', false)){
    \Luminova\Library\Modules::register();
}

/**
 * Register services 
*/
if(is_feature('feature.app.services', true)){
    factory('register');
}

/**
 * Initialize and register class modules and aliases
*/
if(is_feature('feature.app.class.aliases', false) && !defined('INIT_DEV_MODULES')){
    if (file_exists($modules = path('controllers') . 'Config' . DIRECTORY_SEPARATOR . 'Modules.php')) {
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

/**
 * Initialize dev global functions
*/
if(is_feature('feature.app.dev.functions', false) && !defined('INIT_DEV_FUNCTIONS')){
    if(file_exists($global = path('controllers') . 'Utils' . DIRECTORY_SEPARATOR . 'Global.php')){
        define('INIT_DEV_FUNCTIONS', true);
        require $global;
    }
}