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
     * {@inheritdoc}
    */
    protected function onCreate(): void
    {
        /**
         *  Initialize session manager if you want to make use of sessions
         *  @example $session = new Session(new SessionManager());
         *  @example $session->setStorage("my_storage");
         *  @example $session->start();
        */

        /**
        * Register global classes to use across your application life cycle
        * Once exported you can access the method in application class by $this->Foo
        *
        * @example $this->export($session); 
        * @example $this->export(MyClass::class); 
        * @example $this->export(new MyClass(arguments), 'MyClass');
        * @example $this->export(new MyClass(arguments));
        */


        /**
        * Set the template engine 
        * @example $this->setTemplateEngin(parent::SMARTY_TEMPLATE );
        */


        /**
        * Set default the canonical url version for your application
        * Before settings, make sure to register Meta Class 
        * @example $this->export(Meta::class);
        *
        * @example $this->Meta->setCanonicalVersion("https://example.com/", $this->getView());
        * @example $this->Meta->setCanonicalVersion("https://www.example.com/", $this->getView());
        * @example $this->Meta->setCanonicalVersion(parent::baseUrl(), $this->getView());
        */
	}

    /**
     * {@inheritdoc}
    */
    protected function onFinish(): void {}

    /**
     * {@inheritdoc}
    */
    protected function onContextInstalled(string $context): void {}

    /**
     * {@inheritdoc}
    */
    protected function onViewPresent(string $uri): void {}

    /**
     * {@inheritdoc}
    */
    protected function onCommandPresent(array $options): void {}
}