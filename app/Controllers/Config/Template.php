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
   * Specify template engine for rendering your application views.
   * 
   * @var string $templateEngine [default, smarty] 
  */
  public static $templateEngine = 'default';

  /** 
   * Enabling this property will render your view in isolation, allowing template view options to be accessed as variables example: $verName instead of $this->_verName.
   * Additionally, access to protected properties registered in your App\Controller\Application class won't be possible using the $this keyword ($this->myClassName). 
   * Instead, you can use the $self keyword ($self->myClassName).
   * 
   * @var bool $viewIsolation 
  */
  public static bool $viewIsolation = false;

  /** 
   * This property controls the use of variable key prefixing with _ for view options. 
   * When set to false, accessing view options with underscore prefixing will be disabled.
   * 
   * @var bool $useVariablePrefix 
  */
  public static bool $useVariablePrefix = true;

  /** 
   * Set your application template page view cache file directory path.
   * 
   * @var string $pageCacheFolder 
  */
  public static string $pageCacheFolder = 'writeable/caches/optimize';

  /** 
   * Set your application smarty template compile folder.
   * 
   * @var string $smartyCompileFolder 
  */
  public static string $smartyCompileFolder = 'writeable/smarty/compile';

  /** 
   * Set your application smarty template config folder.
   * 
   * @var string $smartyConfigFolder 
  */
  public static string $smartyConfigFolder = 'writeable/smarty/config';

  /** 
   * Set your application smarty template page view cache file directory path.
   * 
   * @var string $smartyCacheFolder 
  */
  public static string $smartyCacheFolder = 'writeable/caches/smarty';
}