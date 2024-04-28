<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Config\Templates\Smarty;

class Classes
{
    /**
     * You can register your classes which will be accessible in smarty template.
     * NOTE: You can also register your class in application controller using export() method.
     * 
     * @return array<string,object> Return array of classes.
     *      -   Use the array key as the class alias name.
    */
    public function getClass(): array
    {
        return [
            //'foo' => new MyFooClass()
        ];
    }
}