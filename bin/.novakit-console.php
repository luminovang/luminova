<?php
declare(strict_types=1);
/**
 * Luminova Framework console command loader.
 *
 * This file is used to register your custom console commands for use in the terminal.
 * You may define commands using either the `Novakit::command()` method or by returning 
 * an associative array of command names mapped to their controller classes.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 * @see https://luminova.ng/docs/0.0.0/commands/novakit
 * @see https://luminova.ng/docs/0.0.0/base/console
 */
use \Luminova\Command\Novakit;
use \App\Console\ConsoleHelloCommand;

// Format 1 – Register command directly, line by line.
// This method optionally supports defining command properties.
Novakit::command('hello', ConsoleHelloCommand::class);

// Format 2 (alternative) – Return a mapped array of commands.
// This format does not support defining command properties.
/*
return [
    'hello' => ConsoleHelloCommand::class
];
*/