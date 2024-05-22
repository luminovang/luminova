<?php 
namespace App\Controllers\Config\Templates\Smarty;

use Smarty\Extension\Base;
use \Luminova\Exceptions\BadMethodCallException;

class Modifiers extends Base 
{
    /**
     * This modifier allows smarty to first check framework function 
     * or dev procural functions before using smarty modifiers.
     * 
     * @param string $function Function called by smarty template.
     * 
     * @return string|null Return function name if is callable, null otherwise.
    */
    public function getModifierCallback(string $function): string|null 
    {
        if (is_callable($function)) {
            return $function;
        }

        throw new BadMethodCallException(sprintf('Function "%s" is not callable.', $function));
    }
}