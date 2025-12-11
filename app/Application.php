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

use \Luminova\Foundation\Core\Application as CoreApplication;

/**
 * @see https://luminova.ng/docs/0.0.0/foundation/application
 * 
 * @example - Initialize the session manager if your application uses sessions.
 * 
 * ```php
 * use Luminova\Sessions\Session;
 * use Luminova\Sessions\Managers\Session as SessionManager;
 * 
 * protected ?Session $session = null;
 * 
 * protected function onCreate(): void 
 * {
 *      $this->session = new Session(new SessionManager());
 *      $this->session->setStorage("my_storage");
 *      $this->session->start();
 * }
 * ```
 * 
 * @example - Register global classes for use throughout the application lifecycle.
 * 
 * - Once exported, you can access the method in the application template view files using `$this->Foo`.
 * - Use the `export` method when the property visibility is **not protected or public**,
 *   or when **view isolation** is enabled.
 *
 * ```php 
 * protected function onCreate(): void 
 * {
 *      $this->view->export($object, 'foo');
 *      $this->view->export(MyClass::class);
 *      $this->view->export(new MyClass(arguments));
 *      $this->view->export(new MyClass(arguments), 'MyClass');
 * }
 * ```
 * 
 * @example - Set the canonical URL version for your application.
 * 
 * - Ensure the `Meta` class is registered before setting this.
 * - Helps with SEO by defining the preferred URL version.
 *
 * ```php 
 * use Luminova\Component\Seo\Schema;
 * 
 * protected ?Schema $schema = null;
 * 
 * protected function onCreate(): void 
 * {
 *      $this->schema = new Schema();
 * 
 *      $uri = $this->getUri();
 * 
 *      $this->schema->setUrl(APP_URL, $uri);
 *      $this->schema->setCanonical("https://example.com/{$uri}");
 *      $this->schema->setCanonical("https://www.example.com/{$uri}");
 * }
 * ```
 */
class Application extends CoreApplication 
{
    /**
     * {@inheritdoc}
     */
    protected function onCreate(): void 
    {
        /**
         * Register namespace for HMVC custom Info module controllers
         * You can remove the below line, if you are not using HMVC 
         * 
         * @see https://luminova.ng/docs/0.0.0/introduction/hmvc-design
         * @see https://luminova.ng/docs/0.0.0/routing/system#lmv-docs-addnamespace
         */
        if(self::$isHmvcModule){
            $this->router->addNamespace('\\App\\Modules\\Info\\Controllers\\');
        }
    }

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
        // E.g, gc_collect_cycles();
    }
}