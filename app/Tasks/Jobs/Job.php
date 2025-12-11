<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Tasks\Jobs;

use Luminova\Command\Input;
use Luminova\Command\Terminal;
use Luminova\Interface\TaskWorkerInterface;

class Job implements TaskWorkerInterface
{
    /**
     * Lazy loaded terminal instance.
     * 
     * @var Input|null $term
     */
    protected ?Input $input = null;

    /**
     * {@inheritDoc}
     */
    public function setCommandInput(Input $input): self
    {
        $this->input = $input;
        return $this;
    }

    /**
     * {@inheritdoc}
     * 
     * Main command execution logic.
     * This method runs when the command is invoked.
     */
    public function run(array $options = []): mixed
    {
        if($this->input === null){
            return 'Invalid terminal object';
        }

        $example = $this->input->getOption('example', 'e');

        Terminal::writeln($example);

        return null;
    }
}