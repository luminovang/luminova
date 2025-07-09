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
use \Luminova\Command\Utils\{Color, Text};
use \Luminova\Foundation\Error\Message;
use function \Luminova\Funcs\filter_paths;

/**
 * @var Message|\Throwable<\T>|null $error
 */

// Initialize terminal
Terminal::init();

if ($error instanceof Throwable) {
    $parts = explode(" File:", $error->getMessage());
    Terminal::writeln(Color::apply('Exception: [' . $error::class . ']', Text::FONT_BOLD, 'red'));
    Terminal::newLine();
    Terminal::error(Message::prettify($parts[0]));
    Terminal::newLine();
    $fileLine = Color::style(isset($parts[1]) 
        ? filter_paths($parts[1])
        : filter_paths($error->getFile() . ' Line: ' . $error->getLine())
    , 'green');
    Terminal::writeln('File: ' . $fileLine);

    $last = $error;

    while ($previous = $last->getPrevious()) {
        $last = $previous;
        $part = explode(" File:", $previous->getMessage());
        Terminal::writeln('Caused by: [' . $previous::class . ']', 'red');
        Terminal::newLine();
        Terminal::error(Message::prettify($part[0]));
        $fileLine = Color::style(isset($part[1]) 
            ? filter_paths($part[1])
            : filter_paths($previous->getFile() . ' Line: ' . $previous->getLine())
        , 'green');
        Terminal::writeln('File: ' . $fileLine);
        Terminal::newLine();
    }
    return;
}

if ($error instanceof Message) {
    Terminal::writeln(Color::apply(
        'Error: [' . $error->getCode() . '] [' . $error->getName() . ']',
        Text::FONT_BOLD, 'red'
    ));
    Terminal::newLine();
    Terminal::error(Message::prettify($error->getMessage()));
    Terminal::newLine();
    return;
}

Terminal::error('Unknown error occurred, please check your error logs for more details');