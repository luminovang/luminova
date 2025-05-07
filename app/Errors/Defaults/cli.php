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
use \Luminova\Command\Utils\Color;

// Initialize terminal
Terminal::init();

if (isset($exception)) {
    $parts = explode(" File:", $exception->getMessage());
    Terminal::error('Exception: [' . $exception::class . ']');
    Terminal::newLine();
    Terminal::writeln($parts[0]);
    $fileLine = Color::style(isset($parts[1]) 
        ? filter_paths($parts[1])
        : filter_paths($exception->getFile() . ' Line: ' . $exception->getLine())
    , 'green');
    Terminal::writeln('File: ' . $fileLine);
    Terminal::newLine();

    $last = $exception;

    while ($previous = $last->getPrevious()) {
        $last = $previous;
        $part = explode(" File:", $previous->getMessage());
        Terminal::error('Caused by: [' . $previous::class . ']');
        Terminal::newLine();
        Terminal::writeln($part[0]);
        $fileLine = Color::style(isset($part[1]) 
            ? filter_paths($part[1])
            : filter_paths($previous->getFile() . ' Line: ' . $previous->getLine())
        , 'green');
        Terminal::writeln('File: ' . $fileLine);
        Terminal::newLine();
    }
    return;
}

if (isset($stack)) {
    Terminal::error('Error: [' . $stack->getCode() . '] [' . $stack->getName() . ']');
    Terminal::writeln($stack->getMessage());
    Terminal::newLine();
    return;
}

Terminal::error('Unknown error occurred, please check your error logs for more details');