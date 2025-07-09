<?php 
/**
 * Luminova Framework Cron job scheduler for managing scheduled tasks.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

use \Luminova\Foundation\Core\CronTasks;

final class Cron extends CronTasks
{
  /**
   * {@inheritdoc}
   * 
   * Schedule the task for execution.
   */
  protected function schedule(): void 
  {
    /**
     * $this->service('\App\Controllers\Cli\ExampleCommand::fooMethod')
     *   ->seconds(5)
     *   ->log(Luminova\Funcs\root('/writeable/log/') . 'cron.log');
     */
  }
}
