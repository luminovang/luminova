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
 * Anonymous function to register class configuration
 * 
 * @return void 
*/
(function(): void {
    $configPath = __DIR__ . '/../../class.config.php';
    if(file_exists($configPath)){
        $config = require_once $configPath;

        if(isset($config['aliases'])){
            foreach ($config['aliases'] as $alias => $namespace) {
                if (!class_alias($namespace, $alias)) {
                    logger('warning', "Failed to create an alias [$alias] for class [$namespace]");
                }
            }
        }
    }
})();