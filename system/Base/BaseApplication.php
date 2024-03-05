<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

namespace Luminova\Base;

use Luminova\Application\Application;

abstract class BaseApplication extends Application {
    
    /**
     * Magic method getter
     * Get properties from template class 
     *
     * @param string $key property or attribute key
     * 
     * @return ?mixed return property else null
    */
    public function __get(string $key): mixed
    {
        $attr = parent::__get($key);
        if($attr === null) {
            return $this->{$key} ?? null;
        }

        return $attr;
    }

}