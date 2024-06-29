<?php 
declare(strict_types=1);
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
if((bool) env('feature.app.autoload.psr4', false)){
    \Luminova\Library\Modules::register();
}

/**
 * Register services 
*/
if((bool) env('feature.app.services', false)){
    factory('register');
}

/**
 * Initialize and register class modules and alias
*/
if((bool) env('feature.app.class.alias', false) && !defined('INIT_DEV_MODULES')){
    if (file_exists($modules = path('controllers') . 'Config' . DIRECTORY_SEPARATOR . 'Modules.php')) {
        define('INIT_DEV_MODULES', true);
        $config = require_once $modules;

        if(isset($config['alias'])){
            foreach ($config['alias'] as $alias => $namespace) {
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
if((bool) env('feature.app.dev.functions', false) && !defined('INIT_DEV_FUNCTIONS')){
    if(file_exists($global = path('controllers') . 'Utils' . DIRECTORY_SEPARATOR . 'Global.php')){
        define('INIT_DEV_FUNCTIONS', true);
        require_once $global;
    }
}