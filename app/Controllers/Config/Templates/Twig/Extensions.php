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

use \Twig\Extension\AbstractExtension;
use \Twig\Extension\GlobalsInterface;
use \App\Controllers\Config\Templates\Twig\Functions;
use \App\Controllers\Config\Templates\Twig\Globals;
use \App\Controllers\Config\Templates\Twig\Operators;
use \App\Controllers\Config\Templates\Twig\Filters;
use \App\Controllers\Config\Templates\Twig\NodeVisitors;
use \App\Controllers\Config\Templates\Twig\TokenParsers;
use \App\Controllers\Config\Templates\Twig\Tests;

class Extensions extends AbstractExtension implements GlobalsInterface
{
   use Functions, Globals, Operators, Filters, NodeVisitors, TokenParsers, Tests;
}