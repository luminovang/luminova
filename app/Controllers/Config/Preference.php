<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

namespace App\Controllers\Config;

use \Luminova\Base\BaseConfig;
use \Luminova\Interface\MailerInterface;
use \Psr\Log\LoggerInterface;

use \Luminova\Email\Clients\NovaMailer;

final class Preference extends BaseConfig
{
    /**
     * Return instance of your preferred logger class.
     * Your logger class must implement psr LoggerInterface.
     * 
     * @example new MyLogger(config) 
     * 
     * @return class-string<LoggerInterface>|null Return preferred logger instance or null to use default logger.
    */
    public function getLogger(): LoggerInterface|null 
    {
        return null;
    }

    /**
     * Return instance of your preferred mail client.
     * Your mail client class must implement psr MailerInterface.
     * You can use \Luminova\Email\Clients\PHPMailer, \Luminova\Email\Clients\SwiftMailer
     * 
     * @example new MyLogger(config) 
     * 
     * @return class-object<MailerInterface>|class-string<MailerInterface>|null Return preferred logger instance or null to use default logger.
    */
    public function getMailer(): MailerInterface|string|null 
    {
        return '\\' . NovaMailer::class;
    }
}