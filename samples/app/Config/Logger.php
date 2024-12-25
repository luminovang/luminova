<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

use \Luminova\Base\BaseConfig;
use \Psr\Log\LoggerInterface;

final class Logger extends BaseConfig
{
    /**
     * Enables asynchronous logging using Fibers to log messages 
     * in a background thread without blocking the UI.
     * 
     * @var bool $asyncLogging Whether to enable asynchronous logging.
     * > Supported for \Luminova\Logger\NovaLogger only.
     */
    public static bool $asyncLogging = false;

    /**
     * Specify the maximum size (in bytes) for each log level (e.g., 10485760 for 10 MB). 
     * When this limit is reached, a backup of the log file is created 
     * if `logger.create.backup` or `$autoBackup` is set to true; otherwise, the logs are cleared.
     * 
     * @var string $maxSize The maximum size of each log file.
     */
    public static int $maxSize = 0;

    /**
     * Indicate whether a backup of log file should be created when the `logger.max.size` or `$maxSize` limit is reached. 
     * Set to 'true' to automatically create a backup and empty the current log file, 'false' to empty the log file only.
     * 
     * @var bool $autoBackup Weather to automatically create backup.
     */
    public static bool $autoBackup = false;

    /**
     * Returns an instance of the preferred logger class, which must 
     * implement the PSR `LoggerInterface`.
     * 
     * @example Return instance of logger class.
     * 
     * ```php 
     * return new MyLogger(config);
     * ```
     * @return class-object<LoggerInterface>|null Preferred logger instance, or `null` to use the default logger.
     */
    public function getLogger(): LoggerInterface|null 
    {
        return null;
    }
}