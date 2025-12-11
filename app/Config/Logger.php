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

use \Luminova\Time\Time;
use \Psr\Log\AbstractLogger;
use \Luminova\Http\Network\IP;
use \Luminova\Logger\NovaLogger;
use \Luminova\Base\Configuration;
use \Luminova\Interface\RequestInterface;

final class Logger extends Configuration
{
    /**
     * Header key to extract and include in the log context, if present.
     * 
     * This is useful for identifying API users or request originators via a specific HTTP header.
     *
     * @var string|null $contextHeaderName
     *
     * @example - Example:
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
     * Enables non-blocking background asynchronous logging.
     * 
     * This setting determines whether remote logging operations are performed asynchronously.
     * Optionally set in environment variable: `logger.remote.async`.
     * 
     * @var bool $remoteAsyncLogging Whether to enable asynchronous logging.
     * > **Note:**
     * > curl maybe disabled in CLI mode on some servers.
     * > Supported for \Luminova\Logger\NovaLogger only.
     */
    public static bool $remoteAsyncLogging = false;

    /**
     * Specify the maximum size (in bytes) for each log level (e.g., 10485760 for 10 MB). 
     * 
     * When this limit is reached, a backup of the log file is created 
     * if `logger.create.backup` or `$autoBackup` is set to true; otherwise, the logs are cleared.
     * 
     * @var int $maxSize The maximum size of each log file.
     */
    public static int $maxSize = 0;

    /**
     * Indicate whether to automatically create a backup of log files.
     * 
     * The backup will be created when the `env(logger.max.size)` or `$maxSize` limit is reached.
     * 
     * Set to:
     * - 'true' to create a backup and empty the current log file in background.
     * - 'false' to empty the log file only.
     * 
     * @var bool $autoBackup Whether to automatically create backup.
     */
    public static bool $autoBackup = false;

    /**
     * Determines whether log context information should be included 
     * when sending logs to the Telegram bot or Slack webhook.
     * 
     * This can also be configured via the environment variable: `logger.remote.send.context`.
     *
     * @var bool
     */
    public static bool $remoteSendContext = false;

    /**
     * {@inheritdoc}
     */
    public static function getEmailLogTemplate(
        RequestInterface $request, 
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