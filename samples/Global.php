<?php
/**
 * This goal file allows you to define your own custom functions
 * or overwrite core procedural functions to replace them with your own. 
 * This file is loaded during the bootstrap process and is called during the framework's execution.
 */
if(!function_exists('myFunction')){
    function myFunction(): string 
    {
        return 'foo';
    }
}