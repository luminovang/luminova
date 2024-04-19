<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use \Luminova\Command\Terminal;

$cli = new Terminal();

if (isset($exception)) {
    $parts = explode(" File:", $exception->getMessage());
    $cli::error('Exception: [' . $exception::class . ']');
    $cli::newLine();
    $cli::writeln($parts[0]);
    $fileLine = isset($parts[1]) ? $cli::color(filter_paths($parts[1]), 'green') : $cli::color(filter_paths($exception->getFile() . ' Line: ' . $exception->getLine()), 'green');
    $cli::writeln('File: ' . $fileLine);
    $cli::newLine();

    $last = $exception;

    while ($previous = $last->getPrevious()) {
        $last = $previous;
        $part = explode(" File:", $previous->getMessage());
        $cli::error('Caused by: [' . $previous::class . ']');
        $cli::newLine();
        $cli::writeln($part[0]);
        $fileLine = isset($part[1]) ? $cli::color(filter_paths($part[1]), 'green') : $cli::color(filter_paths($previous->getFile() . ' Line: ' . $previous->getLine()), 'green');
        $cli::writeln('File: ' . $fileLine);
        $cli::newLine();
    }
} elseif (isset($stack)) {
    $cli::error('Error: [' . $stack->getCode() . '] [' . $name . ']');
    $cli::writeln($stack->getMessage());
    $cli::newLine();
}else{
    $cli::error('Unknown error occurred, please check your error logs for more details');
}