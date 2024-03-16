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
   * Access template view options as variable and render view in isolation mode
   * If set to true then view array options can be access as variable $name instead of $this->_name
   * And $this keyword won't be available to call protected classes registered on App\Controller\Application 
   * This will be use $self->name instead of $this->_name.
   * And to make your class discoverable using $self you need to register your class in App\Controller\Application 
   * @example $this->export(MyClass::class), $this->export(new MyClass()) or with aliases $this->export(new MyClass(), 'myclass')
   * 
   * @var bool $viewIsolation 
  */
  public static bool $viewIsolation = false;

  /** 
   * Disable or enable prefixing variable name with underscore while using $this keyword $this->_name 
   * 
   * @var bool $viewIsolation 
  */
  public static bool $variablePrefix = true;

  /** 
   * Application template optimized file directory path
   * 
   * @var string $optimizerFolder 
  */
  public static string $optimizerFolder = 'writeable/caches/optimize';

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