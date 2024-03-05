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

final class Template 
{ 
    /** 
     * Application template engine
     * 
     * @var string ENGINE [default, smarty] 
    */
    public const ENGINE = 'default';

   /** 
     * Access template view options as variable
     * If set to true then options can be access like $name else $this->_name
     * 
     * @var bool $optionsAsVariable 
    */
    public static bool $optionsAsVariable = false;

    /** 
     * Application template file directory path
     * 
     * @var string $templateFolder 
    */
    public static string $templateFolder = 'resources/views';

    /** 
     * Application template optimized file directory path
     * 
     * @var string $optimizerFolder 
    */
    public static string $optimizerFolder = 'writeable/caches/optimize';

    /** 
     * Holds template assets folder
     * 
     * @var string $assetsFolder 
    */
    public static string $assetsFolder = 'assets';

    /** 
     * Holds smarty template compile folder
     * 
     * @var string $smartyCompileFolder 
    */
    public static string $smartyCompileFolder = 'writeable/smarty/compile';

     /** 
     * Holds template config folder
     * 
     * @var string $smartyConfigFolder 
    */
    public static string $smartyConfigFolder = 'writeable/smarty/config';

     /** 
     * Holds template cache folder
     * 
     * @var string $smartyCacheFolder 
    */
    public static string $smartyCacheFolder = 'writeable/caches/smarty';
    
}