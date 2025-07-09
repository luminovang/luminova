<?php 
/**
 * Luminova Framework Autoload Configuration
 *
 * This file defines class aliases and PSR-4 namespace mappings 
 * for automatic class loading.
 *
 * @package   Luminova
 * @author    Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license   See LICENSE file
 */

return [
    /**
     * Class aliases:
     * 
     * - Allows you to create short names for long class paths.
     * - Example: 'Foo' can refer to 'SomeClass\ExampleFoo' anywhere in your code.
     * - Leave empty if you don't need custom aliases.
     * 
     * > To use this feature set env `feature.app.class.alias` to enable
     */
    'alias' => [
        // 'Foo' => 'SomeClass\ExampleFoo',
    ],

    /**
     * PSR-4 namespace mappings:
     * 
     * @key psr-4
     * 
     * - Maps your namespace to a folder location.
     * - The key is your PHP namespace, and the value is the folder path.
     * - This lets Luminova autoload your classes without manual require/include.
     * 
     * @example: - Register class
     * ```php
     * return [
     *      // ...
     *      'psr-4' => [
     *          '\\Examples\\Psr4Example\\' => '/examples/src/'
     *      ]
     * ];
     * ```
     * @example - Usage:
     * ```php
     * use Examples\Psr4Example\Example;
     * 
     * echo Example::value();
     * ```
     * 
     * > **Note:**
     * > To use this feature set env `feature.app.autoload.psr4` to enable
     */
    'psr-4' => []
];