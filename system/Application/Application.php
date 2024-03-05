<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

namespace Luminova\Application;

use Luminova\Routing\Router;
use Luminova\Config\DotEnv;
use Luminova\Template\TemplateTrait;

class Application 
{
    /**
     * Include TemplateTrait for template method
     * 
     * @method TemplateTrait
    */
    use TemplateTrait;

    /**
     * Base Application instance
     *
     * @var Application|null $instance
    */
    private static ?Application $instance = null;

    /**
     * Router class instance
     *
     * @var Router $router
     */
    public ?Router $router = null;

    /**
     * Initialize the base application constructor
     *
     * @param string $dir The project root directory
     */
    public function __construct(string $dir = __DIR__) {
        // Register dotenv variables
        DotEnv::register(root($dir, '.env'));

        /*
        * Register The Application Timezone
        */
        date_default_timezone_set(env("app.timezone", 'UTC'));
       
        // Initialize the router instance
        $this->router ??= new Router();

        // Set application controller class namespace
        $this->router->addNamespace('\App\Controllers');

        // Initialize the template engine
        $this->initialize(null, $dir);

        // Set the project base path
        $this->setBasePath($this->router->getBase());
    }

    /**
     * Get the current view paths, segments uri
     *
     * @return string
     */
    public function getView(): string 
    {
        return $this->router->getView();
    }

    /**
     * Get application base path from router.
     *
     * @return string
     */
    public function getBase(): string 
    {
        return $this->router->getBase();
    }

    /**
     * Get the base application instance as a singleton.
     *
     * @param string $dir The project root directory
     * 
     * @return Application
     */
    public static function getInstance(string $dir = __DIR__): static 
    {
        return static::$instance ??= new static($dir);
    }
}