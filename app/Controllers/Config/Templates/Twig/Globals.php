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
    public function getGlobals(): array
    {
        return [
            'APP_NAME' => APP_NAME,
            'ENVIRONMENT' => ENVIRONMENT
        ];
    }
}