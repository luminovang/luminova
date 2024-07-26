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
use \Luminova\Application\Foundation;

trait Functions
{
    /**
     * Register your static classes or class name string.
     * Do not initialize class instance in the array.
     * 
     * @var array<string,class-string<\T>> $classes.
    */
    protected static array $classes = [
        'foundation' => Foundation::class
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