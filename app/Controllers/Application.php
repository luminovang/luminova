<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers;

use \Luminova\Base\BaseApplication;

class Application extends BaseApplication 
{
    /**
     * @var Session $session;
     * protected $session;
    */
    public function __construct(string $dir = __DIR__)
    {
        /**
         *  Initialize session manager if you want to make use of sessions
         *  @example $this->session = new Session(new SessionManager());
         *  @example $this->session->setStorage("my_storage");
         *  @example $this->session->start();
        */
       

        /**
        * Register global classes to use across your application life cycle
        * You must register classes before initializing parent __construct
        * 
        * Or you can register your classed using protected method 
        *
        * @example $this->registerClass($session); 
        * @example $this->registerClass(MyClass::class); 
        * @example $this->registerClass("MyClass", new MyClass(arguments));
        * @example this->registerClass(new MyClass(arguments));
        */

        parent::__construct($dir);

        /**
        * Set the template engine 
        * @example $this->setTemplateEngin( parent::SMARTY_TEMPLATE );
        */


        /**
        * Set default the canonical url version for your application
        * Before settings, make sure to register Meta Class 
        * @example $this->registerClass(new Meta(parent::appName(), parent::baseUrl()));
        *
        * @example $this->Meta->setCanonicalVersion("https://example.com/", $this->getView());
        * @example $this->Meta->setCanonicalVersion("https://www.example.com/", $this->getView());
        * @example $this->Meta->setCanonicalVersion(parent::baseUrl(), $this->getView());
        */

	}

}