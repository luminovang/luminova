<?php 
/**
 * Luminova Framework
 * 
 * Twig template extension configuration.
 * Allows registration of functions, classes, filters, globals, and other Twig components.
 *
 * @mixin \Twig\Extension\AbstractExtension<\Twig\Extension\GlobalsInterface>
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config\Templates\Twig;

use \Twig\TwigFunction;

/**
 * Twig helper template functions.
 *
 * Provides access to global functions, constants, static methods, class instantiation,
 * environment variables, request information, and common template helpers directly from Twig templates.
 *
 * @method static mixed fn(string $function, mixed ...$args) Call any global function.
 * @method static mixed const(string $name) Get the value of a defined constant.
 * @method static mixed static(string $class, string $member, mixed ...$args) Call a static member of a class.
 * @method static object<\T> new(string $class, mixed ...$args) Instantiate a class.
 * @method static DateTimeImmutable now() Get the current timestamp as DateTimeImmutable.
 * 
 * @see https://luminova.ng/docs/0.0.0/templates/twig
 * @example - Examples:
 * ```twig
 * {{ new('Example').getValue() }}
 * ```
 */
trait Extensions
{
    /**
     * Register custom functions for Twig templates.
     *
     * These functions are accessible directly in Twig templates.
     *
     * @return TwigFunction[] Array of TwigFunction instances.
     *
     * @example - Example:
     * ```php
     * return [
     *      new TwigFunction('flash', static fn(string $key) => \App\Utils\Messages\flash($key)),
     * ]
     * ```
     */
    public function registerFunctions(): array
    {
        return [];
    }

    /**
     * Register classes accessible in Twig templates.
     *
     * Each key becomes the alias used in the template, and the value
     * is the fully qualified class name.
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
     * Register custom Twig tests.
     *
     * @return array<int,\Twig\TwigTest> Array of TwigTest instances.
     */
    public function getTests(): array
    {
        return [];
    }

    /**
     * Register custom Twig filters.
     *
     * Filters transform template values (e.g., `{{ name|upper }}`).
     *
     * @return array<int,\Twig\TwigFilter> Array of TwigFilter instances.
     *
     * @example - Example:
     * ```php
     * return [
     *     // new TwigFilter('rot13', 'str_rot13'),
     *     // new TwigFilter('upper', 'strtoupper'),
     * ]
     * ```
     */
    public function getFilters(): array
    {
        return [];
    }

    /**
     * Register global constants or variables accessible in all Twig templates.
     *
     * @return array<string,mixed> Associative array of global variables.
     */
    public function getGlobals(): array
    {
        return [
            'APP_NAME'        => APP_NAME,
            'APP_VERSION'     => APP_VERSION,
            'PRODUCTION'      => PRODUCTION,
            'ENVIRONMENT'     => ENVIRONMENT,
            'STATUS_SUCCESS'  => STATUS_SUCCESS,
            'STATUS_ERROR'    => STATUS_ERROR,
            'STATUS_SILENCE'  => STATUS_SILENCE,
            // Add any additional global constants here
        ];
    }

    /**
     * Return custom node visitors for Twig compilation.
     *
     * @return array<int,\Twig\NodeVisitor\NodeVisitorInterface>
     */
    public function getNodeVisitors(): array
    {
        return [];
    }

    /**
     * Register custom Twig operators.
     *
     * @return array{0:string,1:callable} Array containing operator name and handler.
     */
    public function getOperators(): array
    {
        return ['noop', fn($a, $b) => $a];
    }

    /**
     * Return optional provider object for dependency injection or service resolution.
     *
     * @return object|null Return provider object.
     */
    public static function getProvider(): ?object
    {
        return null;
    }

    /**
     * Register custom token parsers for Twig templates.
     *
     * @return array<int,\Twig\TokenParser\TokenParserInterface>
     */
    public function getTokenParsers(): array
    {
        return [];
    }
}