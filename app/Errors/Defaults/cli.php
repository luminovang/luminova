<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use Luminova\Command\Terminal;
use Luminova\Foundation\Error\Message;
use Luminova\Command\Utils\{Color, Text};
use function Luminova\Funcs\display_path;

Terminal::init();

/**
 * @var Message|\Throwable<\T>|null $error
 */

/**
 * Render a single exception block.
 */
if (!function_exists('__render_cli_exception')) {
    function __render_cli_exception(Throwable $e, bool $isRoot = false): void
    {
        $label = $isRoot ? 'Exception' : 'Caused by';

        Terminal::writeln(Color::apply(
            sprintf('%s: [%s]', $label, $e::class),
            Text::FONT_BOLD,
            'red'
        ));

        Terminal::newLine();

        Terminal::error(Message::prettify($e->getMessage()));

        $fileLine = display_path(
            $e->getFile() . ' Line: ' . $e->getLine()
        );

        Terminal::writeln('File: ' . Color::style($fileLine, 'green'));
        Terminal::newLine();
    }
}

if ($error instanceof Throwable) {
    __render_cli_exception($error, true);

    $current = $error;

    while ($current = $current->getPrevious()) {
        __render_cli_exception($current);
    }

    return;
}

if ($error instanceof Message) {
    Terminal::writeln(Color::apply(
        sprintf('Error: [%d] [%s]', $error->getCode(), $error->getName()),
        Text::FONT_BOLD,
        'red'
    ));

    Terminal::newLine();

    Terminal::error(Message::prettify($error->getMessage()));
    Terminal::newLine();

    return;
}

Terminal::error('Unknown error occurred, please check your error logs for more details');