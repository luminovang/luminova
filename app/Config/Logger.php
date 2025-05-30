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
use \Luminova\Interface\HttpRequestInterface;
use \Luminova\Logger\NovaLogger;
use \Luminova\Time\Time;
use \Luminova\Functions\IP;
use \Psr\Log\LoggerInterface;
use \Psr\Log\AbstractLogger;

final class Logger extends BaseConfig
{
    /**
     * Header key to extract and include in the log context, if present.
     * 
     * This is useful for identifying API users or request originators via a specific HTTP header.
     *
     * @var string|null $contextHeaderName
     *
     * @example
     * ```php
     * $contextHeaderName = 'X-API-User-Id';
     * ```
     */
    public static ?string $contextHeaderName = null;

    /**
     * Request body field to extract and include in the log context, if present.
     * 
     * This allows tracking a user or identifier passed in the payload (e.g., `POST`, `GET` etc...).
     *
     * @var string|null $contextFieldName
     *
     * @example
     * ```php
     * $contextFieldName = 'username';
     * ```
     */
    public static ?string $contextFieldName = null;
    
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
     * Determines whether log context information should be included 
     * when sending logs to the Telegram bot.
     * 
     * This can also be configured via the environment variable: `logger.telegram.send.context`.
     *
     * @var bool
     */
    public static bool $telegramSendContext = false;

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
    public function getLogger(): ?LoggerInterface 
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public static function getEmailLogTemplate(
        HttpRequestInterface $request, 
        AbstractLogger $logger,
        string $message, 
        string $level, 
        array $context
    ): ?string 
    {
        $message = ($logger instanceof NovaLogger) 
            ? $logger->message($level, $message, $context, true)
            : $message;
        $url = htmlspecialchars($request->getUrl());
        $method = htmlspecialchars($request->getMethod());
        $userAgent = htmlspecialchars($request->getUserAgent()->toString());
        $when = 'on ' . Time::now()->format('F j, Y, g:i a') . ' by ' . APP_NAME;
        $ip = IP::get();
        $left = ' style="padding: 10px; background-color: #f4f4f4; font-weight: bold; border: 1px solid #ddd;"';
        $right = ' style="padding: 10px; border: 1px solid #ddd;"';

        return <<<HTML
            <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 20px auto; max-width: 600px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                <div style="background-color: #f5f5f5; padding: 15px; border-bottom: 1px solid #ddd;">
                    <h2 style="font-size: 20px; color: #333; margin: 0;">System Log Notification</h2>
                    <p style="font-size: 14px; margin: 5px 0; color: #555;">A new {$level} log entry was generated {$when} system.</p>
                </div>
                <div style="padding: 20px;">
                    {$message}
                    <h3 style="font-size: 18px; color: #333; margin-bottom: 10px;">Request Details</h3>
                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <tr>
                            <th style="text-align: left;font-weight: bold;padding: 8px;">Key</th>
                            <th style="text-align: left;font-weight: bold;padding: 8px;">Value</th>
                        </tr>
                        <tr><td {$left}>URL</td><td {$right}>{$url}</td></tr>
                        <tr><td {$left}>Method</td><td {$right}>{$method}</td></tr>
                        <tr><td {$left}>User Agent</td><td {$right}>{$userAgent}</td></tr>
                        <tr><td {$left}>IP Address</td><td {$right}>{$ip}</td></tr>
                    </table>
                </div>
                <div style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #555; border-top: 1px solid #ddd;">
                    <p>
                        This email was auto-generated by the PHP Luminova System Logger.<br />
                        To disable these messages, set the value to <code>null</code> or remove the email address from <code>env('logger.mail.logs')</code>.
                    </p>
                </div>
            </div>
        HTML;
    }
}