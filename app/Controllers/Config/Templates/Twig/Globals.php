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

/**
 * Register your classes and constants here.
*/
trait Globals
{
    /**
     * To register global constants  or variables.
     * 
     * @return array<string,mixed> Return array of global variables.
    */
    public function getGlobals(): array
    {
        return [];
    }
}