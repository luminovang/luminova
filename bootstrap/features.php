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
if(env('feature.app.autoload.psr4', false)){
    \Luminova\Library\Modules::register();
}

/**
 * Register services 
*/
if(env('feature.app.services', false)){
    factory('register');
}

/**
 * Initialize and register class modules and alias
*/
if(
    env('feature.app.class.alias', false) && 
    !defined('INIT_DEV_MODULES') && 
    file_exists($modules = root('/app/Config/') . 'Modules.php')
) {
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

/**
 * Initialize dev global functions
*/
if(
    env('feature.app.dev.functions', false) && 
    !defined('INIT_DEV_FUNCTIONS') && 
    file_exists($global = root('/app/Utils/') . 'Global.php')
){
    define('INIT_DEV_FUNCTIONS', true);
    require_once $global;
}