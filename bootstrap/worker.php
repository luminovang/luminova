<?php
declare(strict_types=1);
/**
 * PHP background future child process worker.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 * @link https://luminova.ng/docs/0.0.0/components/async-await-fiber
 * @see \Luminova\Component\Async::background()
 */

use \Luminova\Boot;
use \Luminova\Logger\Logger;
use \Luminova\Components\Future\ProcessFuture;

ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('html_errors', '0');

if(!isset($argv)){
    exit(0);
}

if (!function_exists('__get_worker_argv')) {
    /**
     * Parse CLI arguments for worker.
     *
     * @return array Parsed key/value arguments or indexed values.
     */
    function __get_worker_argv(): array
    {
        global $argv;
        static $args = null;

        if ($args !== null) {
            return $args;
        }

        $args = [];
        foreach ($argv as $i => $arg) {
            if ($i === 0) continue;

            if (str_contains($arg, '=')) {
                [$k, $v] = explode('=', $arg, 2);

                if($k === 'arguments'){
                    $args = array_merge($args, (array) unserialize(base64_decode($v)));
                    continue;
                }

                $args[$k] = json_validate($v) ? (json_decode($v, true) ?: []) : $v;
            } else {
                $args[] = $arg;
            }
        }

        return $args;
    }
}

$arguments = __get_worker_argv();
$pid = getmypid();
$pidPipe = $arguments['pid_pipe'] ?: null;

/**
 * Write the PID to the specified pipe (Windows only).
 */
if ($pid && $pidPipe && str_starts_with(PHP_OS, 'WIN')) {
    $pipe = @fopen($pidPipe, 'wb');

    if ($pipe !== false) {
        fwrite($pipe, $pid . PHP_EOL);
        fflush($pipe);
        fclose($pipe);
    }
}

require_once __DIR__ . '/../system/Boot.php';

Boot::cli();
chdir(DOCUMENT_ROOT);
ini_set('display_errors', 'stderr');

$handler = $arguments['__worker_handler__'] ?? null;
$isNoOutput = (bool) ($arguments['__worker_no_output__'] ?? false);
$task = base64_decode($arguments['__worker_task__'] ?? '');

unset(
    $arguments['__worker_handler__'], 
    $arguments['__worker_task__'], 
    $arguments['__worker_no_output__']
);

ob_start();
try {
    if (!$handler || !$task) {
        throw new RuntimeException('No valid handler was passed to background child process.');
    }

    $response = null;

    if ($handler === 'raw') {
        $response = (static function(array $arguments) use ($task): mixed {
            return eval($task);
        })($arguments);
    } else {
        if ($handler === 'closure') {
            $task = \Luminova\Utility\Serializer::unserialize($task);
        }elseif ($handler === 'opis.closure') {
            $task = \Opis\Closure\Serializer::unserialize($task);
        } elseif ($handler === 'class') {
            $task = unserialize($task);
        }

        if (!$task || !is_callable($task)) {
            throw new RuntimeException('Background child process handler is not callable.');
        }

        $response = $task($arguments);
    }

    $output = ob_get_clean() ?: '';

    if (!$isNoOutput && ($response !== null || trim($output) !== '')) {
        if(!ProcessFuture::write($pid, $response, $output)){
            Logger::error(
                "Background child process filed to write response.",
                [
                    'pid'       => $pid,
                    'response'  => $response,
                    'output'    => $output,
                    'arguments' => $arguments
                ]
            );
        }
    }

    exit(0);
} catch (Throwable $e) {
    Logger::error(
        "Background child process execution failed.",
        [
            'pid'       => $pid,
            'message'   => $e->getMessage(),
            'code'      => $e->getCode(),
            'arguments' => $arguments
        ]
    );

    while (ob_get_level()) {
        ob_end_clean();
    }

    exit(1);
}