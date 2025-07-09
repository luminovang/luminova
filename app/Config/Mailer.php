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

use \Luminova\Base\Configuration;
use \Luminova\Interface\MailerInterface;
use \Luminova\Utility\Email\Clients\NovaMailer;

final class Mailer extends Configuration
{
    /**
     * Returns an instance (or class name) of the mail client to use.
     * 
     * The mail client here will be used in your entire application for email services.
     * 
     * - Your mail client must implement `Psr\Mail\MailerInterface`.  
     * - You can use built-in clients like:  
     *   - `\Luminova\Email\Clients\PHPMailer`  
     *   - `\Luminova\Email\Clients\SwiftMailer`  
     * - Return `null` to let the framework use its default mailer.
     * 
     * @example 
     * ```php
     * return new MyMailer($config);
     * ```
     * 
     * @return MailerInterface|class-string<MailerInterface>|null 
     *         Returns a mailer instance, a mailer class name, or `null` for the default mailer.
     */
    public function getMailer(): MailerInterface|string|null 
    {
        return '\\' . NovaMailer::class;
    }
}