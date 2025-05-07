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

use \Twig\TwigFunction;
use \Luminova\Luminova;

trait Functions
{
    /**
     * Register your static classes or class name string.
     * Do not initialize class instance in the array.
     * 
     * @var array<string,string<\T>> $classes.
    */
    protected static array $classes = [
        'luminova' => Luminova::class
    ];

    /**
     * Register functions accessible in Twig templates.
     *
     * @return array<int,TwigFunction> Functions.
    */
    public function registerFunctions(): array
    {
        return [];
    }
}