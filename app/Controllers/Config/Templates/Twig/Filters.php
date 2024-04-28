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

use \Twig\TwigFilter;

trait Filters
{
    public function getFilters(): array
    {
        return [
            //new TwigFilter('rot13', 'str_rot13'),
        ];
    }
}