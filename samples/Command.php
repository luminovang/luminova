<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers;

use Luminova\Base\BaseCommand;

class Command extends BaseCommand 
{
    /**
     * @var string $group command group
    */
    protected string $group = 'test';

    /**
     * @var string $name command name
    */
    protected string $name  = 'command';

    /**
     * @var string|array $usage command usages
    */
    protected string|array $usages  = [
        'php index.php command',
        'php index.php command test'
    ];

    /**
     * @var string $description command description
    */
    protected string $description = 'Print command options';

    /**
     * Options
     *
     * @var array<string, string> $options
    */
    protected array $options = [];


    /**
     * @param array $options command options
     * 
     * @return int 
    */
    public function run(?array $options = []): int
    {
        // Must call explain command first before calling getCommand(), getOptions(), getOption() etc..
        $this->explain($options);

        $command = trim($this->getCommand());

        if($command === 'test'){
            return $this->test();
        }

        var_export($this->getOptions());

        $this->newLine();

        return STATUS_SUCCESS;

    }

    public function test(): int
    {
        $this->print('This is a test command');

        return STATUS_SUCCESS;

    }
}