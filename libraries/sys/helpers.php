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
 * Initialize services
*/
if(is_feature('feature.app.services', true)){
    factory('initializeServices');
}

/**
 * Initialize and register class modules and aliases
*/
$ctlPath = APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR .'Controllers' . DIRECTORY_SEPARATOR;

if(is_feature('feature.app.class.modules', false) && !defined('INIT_DEV_MODULES')){
    $modules = $ctlPath . 'Config' . DIRECTORY_SEPARATOR . 'Modules.php';
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

/**
 * Initialize dev global functions
*/
if(is_feature('feature.app.dev.functions', true) && !defined('INIT_DEV_FUNCTIONS')){

    $global = $ctlPath . 'Utils' . DIRECTORY_SEPARATOR . 'Global.php';

    if(file_exists($global)){
        define('INIT_DEV_FUNCTIONS', true);
        require $global;
    }
}