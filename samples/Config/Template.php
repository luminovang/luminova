<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Config;

/**
 * @see https://luminova.ng/public/docs/0.0.0/templates/config
*/
final class Template 
{ 
    /** 
     * Specify the template engine for rendering your application views.
     * Available Template Engines: [default, twig, or smarty]
     * 
     * @var string $templateEngine Engine name (default: default).
     *    - `smarty`: Configuration files in /app/Controllers/Config/Templates/Smarty/
     *    - `twig`: Configuration files in /app/Controllers/Config/Templates/Twig/
     *    - `default`: No additional configuration is required.
     */
    public static string $templateEngine = 'default';

    /** 
     * Enabling this property will render your view in isolation, allowing template view options to be accessed as variables (e.g., $verName instead of $this->_verName).
     * Additionally, access to protected properties registered in your App\Controller\Application class won't be possible using the $this keyword ($this->myClassName). 
     * Instead, you can use the $self keyword ($self->myClassName) and `export()` method in application controller to export your classes.
     * 
     * @var bool $templateIsolation 
    */
    public static bool $templateIsolation = false;

    /** 
     * This property controls the use of variable key prefixing with `_` for view options. 
     * When set to false, accessing view options with underscore prefixing will be disabled.
     * 
     * @var bool $variablePrefixing
     *  > Note: You must specify your variable type before building, your application you can't change this option after building.
     *  > Changing it later will cause view options not to be discovered.
     */
    public static bool $variablePrefixing = true;

    /** 
     * Set your application template cache file directory path.
     * 
     * @var string $cacheFolder 
     */
    public static string $cacheFolder = 'writeable/caches/';

    /** 
     * Set your application template engine compile folder.
     * 
     * @var string $compileFolder 
     */
    public static string $compileFolder = 'writeable/compile/';

    /** 
     * Set your application template engine config folder.
     * 
     * @var string $configFolder 
     */
    public static string $configFolder = 'writeable/config/';
}