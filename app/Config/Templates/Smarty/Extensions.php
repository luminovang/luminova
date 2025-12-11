<?php 
/**
 * Luminova Framework
 *
 * Configuration for Smarty template extensions.
 * Allows registration of classes, objects, functions, and modifiers
 * accessible within Smarty templates.
 *
 * @package Luminova
 * @author  Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config\Templates\Smarty;

use \Luminova\Template\Engines\Extensions\SmartyFunction;

/**
 * Smarty helper template functions.
 *
 * Provides access to global functions, constants, static methods, class instantiation,
 * environment variables, request information, and common template helpers directly from Smarty templates.
 *
 * @method static mixed fn(string $function, mixed ...$args) Call any global function.
 * @method static mixed const(string $name) Get the value of a defined constant.
 * @method static mixed static(string $class, string $member, mixed ...$args) Call a static member of a class.
 * @method static object<\T> new(string $class, mixed ...$args) Instantiate a class.
 * @method static DateTimeImmutable now() Get the current timestamp as DateTimeImmutable.
 * 
 * @see https://luminova.ng/docs/0.0.0/templates/smarty
 * @example - Examples:
 * ```tpl
 * { new('Example')->getValue() }
 * ```
 */
final class Extensions
{
    /**
     * Register class names that will be accessible inside Smarty templates.
     *
     * Each key becomes the alias used in the template, and the value is the
     * fully qualified class name.
     *
     * @return array<string,class-string> Associative array of class aliases.
     *
     * @example - Example:
     * ```php
     * return [
     *     'Example' => \App\Utils\Example::class,
     * ]
     * ```
     * > **Note:**
     * > Do not initialize class instance in the array.
     */
    public function registerClasses(): array
    {
        return [];
    }

    /**
     * Register class instances that should be available inside Smarty templates.
     *
     * You can expose any object to the template layer. Methods or properties
     * will be accessible based on Smarty’s object rules.
     *
     * Luminova also supports anonymous object handlers, allowing Smarty to call
     * classes, functions, or constants automatically without manual registration.
     * Use this array only when you want full control over what instances are exposed.
     *
     * @return array<string, object> Associative array of object aliases.
     *
     * @example - Example:
     * ```
     * return [
     *     'example' => new \App\Utils\Example('foo'),
     * ]
     * ```
     * Accessing Object:
     * ```tpl
     * {{ $example assign='obj' }}
     * {{ $obj->getValue() }}
     * ```
     * > **Note:**
     * > You may also export objects in your controller using `$this->view->export()`.
     */
    public function registerObjects(): array
    {
        return [];
    }

    /**
     * Register global variables accessible in all Smarty templates.
     *
     * @return array<string,mixed> Associative array of global variables.
     * 
     * @example - Example:
     * ```php
     * return [
     *     'EXAMPLE' => 'Foo Value',
     * ]
     * ```
     * Accessing Global Variable:
     * ```tpl
     * {{ $EXAMPLE }}
     * ```
     */
    public function registerGlobals(): array
    {
        return [];
    }

    /**
     * Register custom functions available to Smarty templates.
     *
     * These are handled through Luminova's `SmartyFunction` wrapper, which
     * mirrors the behavior of Twig-style function registration.
     *
     * @return SmartyFunction[] List of registered functions.
     *
     * @example - Example:
     * ```php
     * return [
     *      new SmartyFunction(
     *          'flash', 
     *          static fn(string $status):string => \App\Utils\Messages\flash($status), 
     *          format: false
     *      ),
     *      new SmartyFunction('hello', static fn(string $name) => "Hello {$name}!")
     * ]
     * ```
     * 
     * Accessing Functions:
     * ```tpl
     * {{ flash key='success' }}
     * {{ hello name='success' }}
     * ```
     */
    public function registerFunctions(): array
    {
        return [];
    }

    /**
     * Register modifiers that can be used inside Smarty templates.
     *
     * Modifiers transform template values (e.g., `{$name|upper}`).  
     * Luminova allows you to override or extend Smarty’s default modifiers.
     *
     * @return array<string,callable|string> Modifier name mapped to a callable.
     * @example - Example:
     * ```php
     * return [
     *      'upper'   => 'strtoupper',
     *      'lower'   => 'strtolower',
     *      'reverse' => fn($v) => strrev($v),
     * ];
     * ```
     */
    public function registerModifiers(): array
    {
        return [];
    }
}