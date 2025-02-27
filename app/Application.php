<?php 
/**
 * Luminova Framework application class.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App;

use \Luminova\Core\CoreApplication;

/**
 * @example - Initialize the session manager if your application uses sessions.
 * 
 * ```php
 * use Luminova\Sessions\Session;
 * protected ?Session $session = null;
 * 
 * $this->session = new Session(new SessionManager());
 * $this->session->setStorage("my_storage");
 * $this->session->start();
 * ```
 * 
 * @example - Register global classes for use throughout the application lifecycle.
 * 
 * - Once exported, you can access the method in the application template view files using `$this->Foo`.
 * - Use the `export` method when the property visibility is **not protected or public**,
 *   or when **view isolation** is enabled.
 *
 * ```php 
 * $this->export($this->session);
 * $this->export(MyClass::class);
 * $this->export(new MyClass(arguments), 'MyClass');
 * $this->export(new MyClass(arguments));
 * ```
 * 
 * @example - Set the canonical URL version for your application.
 * 
 * - Ensure the `Meta` class is registered before setting this.
 * - Helps with SEO by defining the preferred URL version.
 *
 * ```php 
 * $this->export(Meta::class); //or $this->Meta = new Meta();
 * $this->Meta->setCanonicalVersion("https://example.com/", $this->getView());
 * $this->Meta->setCanonicalVersion("https://www.example.com/", $this->getView());
 * $this->Meta->setCanonicalVersion(parent::baseUrl(), $this->getView());
 * ```
 */
class Application extends CoreApplication 
{
    /**
     * {@inheritdoc}
     */
    protected function onCreate(): void {}

    /**
     * {@inheritdoc}
     */
    protected function onFinish(array $info): void {}

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

    /**
     * {@inheritdoc}
     */
    protected function onDestroy(): void 
    {
        // Optional garbage collection
        // gc_collect_cycles();
    }
}