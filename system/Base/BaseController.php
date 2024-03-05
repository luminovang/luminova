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
use \Luminova\Http\Request;
use \Luminova\Security\InputValidator;
use \App\Controllers\Application;

abstract class BaseController  extends ViewController
{
    /**
     * HTTP request object 
     * @var Request $request 
    */
    protected ?Request $request = null;

    /**
     * Input validation object 
     * @var InputValidator $validate
    */
    protected ?InputValidator $validate = null;

     /**
      * Application instance
      * @var Application $app 
     */
    protected ?Application $app = null;
 
    /**
     * Initialize controller instance
     * Make request and validate available global
     * 
     * @var InputValidator $validate $this->validate
     * @var Request $request $this->request
     * @var Request $app  $this->app
    */
    public function __construct()
    {
        $this->validate = $this->validate();
        $this->request = $this->request();
        $this->app = $this->app();
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