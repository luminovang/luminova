<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config\Templates\Twig;

use \Twig\Extension\AbstractExtension;
use \Twig\Extension\GlobalsInterface;
use \Twig\TwigFunction;
use \App\Config\Templates\Twig\{
   Functions, Globals, Operators, Filters, NodeVisitors, TokenParsers, Tests
};
use \Luminova\Exceptions\BadMethodCallException;
use \Throwable;

class Extensions extends AbstractExtension implements GlobalsInterface
{
   use Functions, Globals, Operators, Filters, NodeVisitors, TokenParsers, Tests;

    /**
     * Register functions accessible in Twig templates.
     *
     * @return array<int,TwigFunction> Functions.
    */
    public function getFunctions(): array
    {
        return array_merge([
            /** 
             * `call_func` Call global functions,
             * 
             * @example To call any global functions.
             *     {{call_func('functionName', ..,arguments)}}
             * 
             * @param string $function The function name to call.
             * @param mixed ...$arguments Function arguments,
             * 
             * @return mixed The response from called function.
             * @throws BadMethodCallException
            */
            new TwigFunction('call_func', function (string $function, mixed ...$arguments): mixed {
                if (is_callable($function)) {
                    return $function(...$arguments);
                }

                throw new BadMethodCallException(sprintf('Function "%s" is not callable.', $function));
            }),

            /** 
             * `const` Access a global constant variables.
             * 
             * @example To get a const variable.
             *     {{get_const('APP_NAME')}}
             * 
             * @param string $constant The constant name
             * 
             * @return mixed The value of called constant.
             * @throws BadMethodCallException
            */
            new TwigFunction('get_const', function (string $constant): mixed {
                if (defined($constant)) {
                    return constant($constant);
                }

                throw new BadMethodCallException(sprintf('Constant variable "%s" is not defined.', $constant));
            }),

            /** 
             * `call_static` Class class static method,
             * 
             * @example To call any static method.
             *     Namespace: {{call_static('\Foo\Bar\Baz', 'myMethod', ..,arguments)}}
             *     Alias: {{call_static('foo', 'myMethod', ..,arguments)}}
             * 
             * @param string|class-string $class The class name to class it method.
             * @param string $method The class class method name to call.
             * @param mixed ...$arguments Method arguments,
             * 
             * @return mixed The response of called method.
             * @throws BadMethodCallException
            */
            new TwigFunction('call_static', function (string $class, string $method, mixed ...$arguments): mixed {
                $class = static::$classes[$class] ?? $class;

                if (method_exists($class, $method)) {
                    return $class::{$method}(...$arguments);
                }

                throw new BadMethodCallException(sprintf('Method "%s" or class "%s" does not exist', $method, $class));
            }),

            /** 
             * `new` Initializes class instance,
             * 
             * @example To initialize new class instance.
             *     Namespace: {{call_new('\Foo\Bar\Baz', ...arguments)}}
             *     Alias: {{call_new('foo', ...arguments)}}
             * 
             * @param string|class-string $class The class name to initialize.
             * @param mixed ...$arguments Class initialization arguments,
             * 
             * @return class-object The class object.
             * @throws BadMethodCallException
            */
            new TwigFunction('call_new', function (string $class, mixed ...$arguments): object {
                $class = static::$classes[$class] ?? $class;
                try{
                    if (class_exists($class)) {
                        return new $class(...$arguments);
                    }
                }catch(Throwable $e){
                    throw new BadMethodCallException(sprintf(
                        'Unable to initialize class "%s", %s', 
                        $class, 
                        $e->getMessage()
                    ), $e->getCode(), $e);
                }

                throw new BadMethodCallException(sprintf('Class "%s" does not exist', $class));
            })
        ], $this->registerFunctions());
    }
}