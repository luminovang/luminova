<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Config\Templates\Twig;

use \Twig\TwigFunction;

trait Functions
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('app', 'app'),
            new TwigFunction('href', 'href'),
            new TwigFunction('asset', 'asset'),
            new TwigFunction('locale', 'locale'),
            new TwigFunction('request', 'request'),
            new TwigFunction('func', 'func')
        ];
    }
}