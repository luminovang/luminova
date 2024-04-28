<?php 
namespace App\Controllers\Config\Templates\Smarty;

use Smarty\Extension\Base;

class Modifiers extends Base 
{
    /**
     * This modifier allows smarty to first check framework function 
     * or dev procural functions before using smarty modifiers.
     * 
     * @param string $callable Function called by smarty template.
     * 
     * @return string|null Return function name if is callable, null otherwise.
    */
    public function getModifierCallback(string $callable): string|null 
    {

        if (is_callable($callable)) {
            return $callable;
        }

        return null;
    }
}