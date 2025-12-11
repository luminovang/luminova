<?php 
/**
 * Luminova Framework application background tasks controller.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Tasks;

use \Closure;
use \Luminova\Base\Queue;

class TaskQueue extends Queue
{
    /**
     * {@inheritDoc}
     */
    protected string $table = '`task_queue`';

    /**
     * {@inheritDoc}
     */
    protected ?string $group = null;

    /**
     * {@inheritDoc}
     */
    protected bool $deleteOnCompletion = false;

    /**
     * {@inheritDoc}
     */
    protected ?string $stopSignalFile = null;

    /**
     * {@inheritDoc}
     */
    public bool $eventLogging = true;

    /**
     * {@inheritDoc}
     */
    protected bool $isClosureSupported = true;

    /**
     * {@inheritDoc}
     */
    protected ?Closure $onError = null;

    // /**
    //  * {@inheritDoc}
    //  */
    // public function __construct()
    // {
    //    parent::__construct();
    //    $this->onError = function(int $id, string $status, Throwable $e){
    //        // Handle error
    //    };
    // }

    /**
     * {@inheritDoc}
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return parent::__callStatic($method, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    protected function onCreate(): void 
    {
        // $this->stage(
        //    handler: 'App\Tasks\Jobs\SystemTasks::run',
        //    arguments: ['API_STATUS'],
        //    forever: 1440, // Run once every 24 hours
        //    priority: 3
        // )->stage(
        //    handler: 'App\Tasks\Jobs\SystemTasks::run',
        //    arguments: ['CLEAR_CACHES'],
        //    forever: 1440,
        //    priority: 5
        // );
    }

    /**
     * {@inheritDoc}
     */
    protected function tasks(): ?array
    {
        // Optionally prevent accessing task outside of cli
        //
        // if($this->mode !== 'cli'){
        //    return null;
        // }
        //
        // return [
        //      ['handler' => 'App\Foo\Class@methodName', 'arguments' => ['foo', 'bar', true, false, 0]],
        //      ['handler' => 'App\Foo\Class::staticMethodName', 'arguments' => [...]],
        //      [new Luminova\Models\Task(['handler' => 'App\Utils\Cleaner::purgeTemp', 'forever' => 1440])
        // ];

        return [
            [
                'handler' => '\App\Tasks\Workers\Job@run', 
                'arguments' => [],
                'forever' => 720,
                'priority' => 3
            ]
        ];
    }
}