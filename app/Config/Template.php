<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

/**
 * @see https://luminova.ng/docs/0.0.0/templates/config
*/
final class Template 
{ 
    /** 
     * Specifies the template engine used for rendering application views.
     * 
     * Supported template engines:  
     * - `default`  → No additional configuration required.  
     * - `smarty`   → Configuration in `/app/Config/Templates/Smarty/`.  
     * - `twig`     → Configuration in `/app/Config/Templates/Twig/`.  
     * 
     * @var string $templateEngine Default: `default`
     */
    public string $templateEngine = 'default';

    /** 
     * Enables template isolation, preventing direct access to properties 
     * in `App\Application` via `$this->propertyName`.  
     * 
     * When enabled:  
     * - Use `$self->propertyName` instead of `$this->propertyName`.  
     * - Use the `$this->export(...)` method in application class or `$this->app->export(...)` int the controller to expose properties.  
     * - Template variables can be accessed directly as `$varName` instead of `$this->varName`.  
     * 
     * @var bool $templateIsolation Default: `false`
     */
    public bool $templateIsolation = false;

    /**
     * Controls the use of an underscore `_` prefix for view option variables.  
     * 
     * - `true`  → Variables will be prefixed with `_` (e.g., `$_varName`).  
     * - `false` → Underscore prefixing is disabled.  
     * - `null`  → Variables are stored as raw arrays (`$options`).  
     * 
     * **Important:** This setting must be defined **before** building your application.  
     * Changing it afterward will prevent view options from being properly recognized.  
     * 
     * @var bool|null $variablePrefixing Default: `true`
     */
    public bool|null $variablePrefixing = true;

    /** 
     * Directory path for cached template files.
     * 
     * @var string $cacheFolder Default: `writeable/caches/`
     */
    public string $cacheFolder = 'writeable/caches/';

    /** 
     * Directory path for compiled template files.
     * 
     * @var string $compileFolder Default: `writeable/compile/`
     */
    public string $compileFolder = 'writeable/compile/';

    /** 
     * Directory path for template engine configuration files.
     * 
     * @var string $configFolder Default: `writeable/config/`
     */
    public string $configFolder = 'writeable/config/';
}