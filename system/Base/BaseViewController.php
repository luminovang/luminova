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

use Luminova\Controllers\ViewController;

abstract class BaseViewController  extends ViewController
{
    /**
     * Initialize controller instance
    */
    public function __construct()
    {
        $this->onCreate();
    }

    /**
     * Uninitialized controller instance
    */
    public function __destruct() {
        $this->onDestroy();
    }
    
    /**
     * Magic method getter
     *
     * @param string $key property key
     * 
     * @return ?mixed return property else null
    */
    public function __get(string $key): mixed
    {
        return $this->{$key} ?? null;
    }
    
     /**
     * Magic method isset
     * Check if property is set
     *
     * @param string $key property key
     * 
     * @return bool 
    */
    public function __isset(string $key): bool
    {
        return isset($this->{$key});
    }
}