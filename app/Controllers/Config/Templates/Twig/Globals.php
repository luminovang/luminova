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
     * To register global const variables.
     * 
     * @return array<string,string> Return array of global variables.
    */
    public function getGlobals(): array
    {
        return [];
    }
}