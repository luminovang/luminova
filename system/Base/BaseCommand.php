<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace Luminova\Base;

use Luminova\Command\Terminal;

abstract class BaseCommand extends Terminal 
{

    /**
     * @var string $group command group
    */
    protected string $group = '';

    /**
     * @var string $name command name
    */
    protected string $name = '';

    /**
     * @var string|array $usage command usages
    */
    protected string|array $usage = '';

    /**
     * @var array<string, mixed> $options command options
    */
    protected array $options = [];

    /**
     * @var string $description command description
    */
    protected string $description = '';

    /**
     * Run a command.
     *
     * @param array<string, mixed> $params
     * 
     * @return int status code 1 or 0
    */
    abstract public function run(?array $params = []): int;

    /**
     * Magic method getter
     *
     * @param string $key property key
     * 
     * @return mixed return property else null
    */
    public function __get(string $key): mixed
    {
        return $this->{$key} ?? null;
    }
    
     /**
     * Magic method isset
     * Check if property is set
     *
     * @param string $key property key
     * 
     * @return bool 
    */
    public function __isset(string $key): bool
    {
        return isset($this->{$key});
    }
}