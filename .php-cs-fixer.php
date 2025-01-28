<?php 
declare(strict_types=1);
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use \Luminova\Debugger\PhpCsFixer;

/**
 * Run command: composer cs-fix
 */
$header = <<<HEADER
My Application.

This file is part of the "My Application" project.

@package MyApplication
@author Dav [or Dav's full name]
@copyright (c) yyyy Dav
@license MIT License (or any other license type)
@link https://myapplication.example.com
HEADER;

return (new PhpCsFixer(__DIR__))
    ->setHeaderComment($header)
    ->getRules();