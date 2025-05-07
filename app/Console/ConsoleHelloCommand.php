<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Console;

use \Luminova\Base\BaseConsole;

/**
 * This class demonstrates how to create a CLI console command 
 * that can be executed using `novakit` without relying on the routing system.
 * 
 * @see /bin/.novakit-console.php
 */
class ConsoleHelloCommand extends BaseConsole 
{
    /**
     * {@inheritdoc}
     */
    protected string $group = 'Demo';

    /**
     * {@inheritdoc}
     */
    protected string $name = 'hello';

    /**
     * {@inheritdoc}
     */
    protected array|string $usages = [
        'php novakit hello --name=User',
        'php novakit hello --help'
    ];

    /**
     * {@inheritdoc}
     */
    protected array $options = [
        '-n, --name' => 'Name to greet'
    ];

    /**
     * {@inheritdoc}
     */
    protected array $examples = [
        'php novakit hello --name=Peter' => 'Greets Peter',
        'php novakit hello -n=Alex' => 'Greets Alex using alias'
    ];

    /**
     * {@inheritdoc}
     */
    protected string $description = 'Greets a user by name.';

    /**
     * {@inheritdoc}
     * 
     * Return STATUS_ERROR to use default luminova help output style. 
     */
    public function help(array $helps): int
    {
        return STATUS_ERROR;
    }

    /**
     * {@inheritdoc}
     * 
     * Main command execution logic.
     * This method runs when the command is invoked.
     */
    public function run(?array $options = []): int
    {
        // Parse the received command-line options
        $this->term->perse($options);

        // Access named option or alias (e.g., --name or -n)
        $name = $this->term->getAnyOption('name', 'n') ?? 'World';

        $this->term->writeln("Hello {$name}!");

        return STATUS_SUCCESS;
    }
}