<?php 
/**
 * Luminova Framework Template and View  rendering Configuration.
 *
 * @package   Luminova
 * @author    Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license   See LICENSE file
 */
namespace App\Config;

/**
 * Template configuration for Luminova views.
 *
 * @see https://luminova.ng/docs/0.0.0/templates/config
 */
final class Template 
{ 
    /** 
     * The template engine used to render application views.
     * 
     * Supported options:  
     * - `default` → No extra configuration required {@see enableDefaultTemplateLayout}
     * - `smarty`  → Configure under `/app/Config/Templates/Smarty/`  
     * - `twig`    → Configure under `/app/Config/Templates/Twig/`  
     * 
     * @var string $templateEngine (default: `default`)
     */
    public string $templateEngine = 'default';

    /** 
     * Enables template isolation to prevent direct access to properties 
     * of `App\Application` via `$this->propertyName`.  
     * 
     * When enabled:  
     * - Use `$self->propertyName` instead of `$this->propertyName`.  
     * - Expose properties via `$this->view->export(...)` in the application 
     *   class or `$this->app->view->export(...)` in controllers.  
     * - Template variables are accessible directly as `$varName` instead 
     *   of `$this->varName`.  
     * 
     * @var bool $templateIsolation (default: `false`)
     */
    public bool $templateIsolation = false;

    /**
     * Enable or disable the default template layout.
     *
     * When enabled (`true`) and `$templateEngine` is set to `default`, 
     * you can use the built-in layout system for template inheritance. 
     * This allows you to extend parts of the layout easily, for example:
     *
     * @example - Example:
     * ```php
     * <?php $tpl = $this->layout->template('/layouts/scaffolding'); ?>
     * 
     * <?= $tpl->extend('head'); ?>
     * <?= $tpl->extend('section'); ?>
     * <?= $tpl->extend('footer'); ?>
     * ```
     *
     * > When disabled (`false`), the `$this->layout` object in templates will be `NULL`.
     * To use layouts in that case, you must set them up manually.
     *
     * @var bool $enableDefaultTemplateLayout (default: `false`)
     * @see https://luminova.ng/docs/0.0.0/templates/php-layout-engine
     */
    public bool $enableDefaultTemplateLayout = false;

    /**
     * Controls whether view option variables are prefixed with an underscore `_`.  
     * 
     * - `true`  → Variables use the `_` prefix (e.g., `$_varName`)  
     * - `false` → No prefix added  
     * - `null`  → Variables are stored as raw arrays (`$options`)  
     * 
     * **Note:** This must be set **before** building your application.  
     * Changing it later will break template option recognition.  
     * 
     * @var bool|null $variablePrefixing (default: `true`)
     */
    public ?bool $variablePrefixing = true;

    /** 
     * Directory for compiled template files.
     * 
     * Directory by template engines for when compiling templates.
     * 
     * @var string $compileFolder (default: `writeable/compile/`)
     */
    public string $compileFolder = 'writeable/compile/';

    /** 
     * Directory for template engine configuration files.
     * 
     * @var string $configFolder (default: `writeable/config/`)
     */
    public string $configFolder = 'writeable/config/';
}