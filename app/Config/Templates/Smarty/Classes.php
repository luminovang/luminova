<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config\Templates\Smarty;

use \Luminova\Exceptions\BadMethodCallException;
use \Luminova\Application\Foundation;

final class Classes
{
    /**
     * Initialized class context.
     * Do not set your classed here.
     * 
     * @var array<string,object> $instance
    */
    private static array $instance = [];

    /**
     * Register your static classes or class name string.
     * Do not initialize class instance in the array.
     * Use the array key as the class alias name.
     * 
     * @var array<string,class-string> $classes.
    */
    private static array $classes = [
        'foundation' => Foundation::class
    ];

    /**
     * To register your classes which will be accessible in smarty template.
     * 
     * @return array<string,class-string> Return array of classes.
     *      -   Use the array key as the class alias name.
    */
    public function registerClasses(): array
    {
        return [];
    }

    /**
     * You can register your classes which will be accessible in smarty template.
     * NOTE: You can also register your class in application controller using export() method.
     * 
     * @return array<string,class-object> Return array of classes.
     *      -   Use the array key as the class alias name.
    */
    public function registerObjects(): array
    {
        return [
            'call_func' => static::newInstance('call_func'),
            'get_const' => static::newInstance('get_const'),
            'call_new' => static::newInstance('call_new'),
            'call_static' => static::newInstance('call_static')
        ];
    }

    /**
     * Anonymous class implementation to handle smarty class calls.
     * This implementation allows you to call any class, function or access constant variables 
     * Without having to manfully register it.
     * 
     * @example - {call_static->foundation method='copyright'}
     * @example - {call_static->instance class='\App\File\Caller' method='copyright'}
     * @example - {call_func->request param='copyright'}
     * @example - {get_const->CONSTANT}
     * @example - {call_new->request param='copyright'}
     * @example - {call_new->instance class='\App\File\Caller'}
     * @example - {call_new->instance class='\Luminova\Application\Foundation' assign='foundation'}
     * @example - {new->foundation assign='foundation'} 
     *          - Access {$foundation->version()}
     * 
     * @param string $context The caller context.
     * @param array<string,class-string> $classes The registered classes.
     * 
     * @return class-object<anonymous> Return anonymous class object.
    */
    private static function newInstance(string $context): object 
    {
        static::$instance[$context] ??= new class($context, static::$classes){
            /**
             * @var array<string,class-string> $classes
            */
            private static array $classes = [];

            /**
             * @var string $context
            */
            private string $context = 'call_func';

            /**
             * @param string $context
             * @param array<string,class-string> $classes
            */
            public function __construct(string $context = 'call_func', array $classes = [])
            {
                static::$classes = $classes;
                $this->context = $context;
            }

            /**
             * @param string $method
             * @param array<int,mixed> $arguments
             * 
             * @return mixed Response.
            */
            public function __call(string $method, array $arguments): mixed
            {
                // Handle function calls
                if ($this->context === 'call_func') {
                    if (is_callable($method)) {
                        $arguments = array_values($arguments[0]);
                        return $method(...$arguments);
                    }
                    throw new BadMethodCallException(sprintf('Function "%s" is not callable.', $method));
                }

                // Handle constant variables
                if ($this->context === 'get_const') {
                    if (defined($method)) {
                        return constant($method);
                    }
                    throw new BadMethodCallException(sprintf('Constant variable "%s" is not defined.', $method));
                }

                // Handle static method calls
                if ($this->context === 'call_static') {
                    if ($method === 'instance') {
                        $class = $arguments[0]['class'] ?? null;
                        $method = $arguments[0]['method'] ?? null;

                        if ($class === null) {
                            throw new BadMethodCallException('You need to pass the class name in the argument: "class", e.g., "{call_static->instance class=\'\\App\\File\\Caller\' method=\'methodName\'}".');
                        }

                        if ($method === null) {
                            throw new BadMethodCallException('You need to pass the method name in the argument: "method", e.g., "{call_static->className method=\'methodName\'}".');
                        }

                        unset($arguments[0]['class'], $arguments[0]['method']);
                    }else{
                        $class = static::$classes[$method] ?? $method;
                        $method = $arguments[0]['method'] ?? null;
                        if ($method === null) {
                            throw new BadMethodCallException('You need to pass the method name in the argument: "method", e.g., "{call_static->className method=\'methodName\'}".');
                        }
                        unset($arguments[0]['method']);
                    }

                    $arguments = array_values($arguments[0]);
                    return $class::$method(...$arguments);
                }

                // Handle instance creation
                if ($this->context === 'call_new') {
                    if ($method === 'instance') {
                        $class = $arguments[0]['class'] ?? null;

                        if ($class === null) {
                            throw new BadMethodCallException('You need to pass the class name in the argument: "class", e.g., "{call_new->instance class=\'\\App\\File\\Caller\'}".');
                        }

                        unset($arguments[0]['class']);
                        $arguments = array_values($arguments[0]);
                        return new $class(...$arguments);
                    }

                    $class = static::$classes[$method] ?? null;
                    if ($class === null) {
                        throw new BadMethodCallException(sprintf('Class "%s" does not exist.', $method));
                    }

                    $arguments = array_values($arguments[0]);
                    return new $class(...$arguments);
                }

                return null;
            }
        };

        return static::$instance[$context];
    }
}