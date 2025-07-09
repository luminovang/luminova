<?php 
declare(strict_types=1);
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
use function \Luminova\Funcs\import;

/**
 * Autoload register PSR-4 classes.
 */
if (env('feature.app.autoload.psr4', false)) {
    \Luminova\Foundation\Module\Autoloader::register();
}

/**
 * Register application services.
 */
if (env('feature.app.services', false)) {
    \Luminova\Foundation\Module\Factory::register();
}

/**
 * Initialize and register class modules and aliases.
 */
if (!defined('__INIT_DEV_MODULES__') && env('feature.app.class.alias', false)) {
    $config = import('app:Config/Modules.php', true, true);

    if (is_array($config['alias'] ?? null)) {
        foreach ($config['alias'] as $alias => $namespace) {
            if (!class_alias($namespace, $alias)) {
                \Luminova\Funcs\logger('warning', "Failed to create alias [$alias] for class [$namespace]");
            }
        }
    }

    define('__INIT_DEV_MODULES__', true);
}

/**
 * Load and initialize dev global functions.
 */
if (!defined('__INIT_DEV_FUNCTIONS__') && env('feature.app.dev.functions', false)) {
    import('app:Utils/Global.php', true, true);
    define('__INIT_DEV_FUNCTIONS__', true);
}
