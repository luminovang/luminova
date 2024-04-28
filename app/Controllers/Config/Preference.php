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

use \Psr\Log\LoggerInterface;

final class Preference
{
    /**
     * Return instance of your preferred logger class.
     * Your logger class must implement psr LoggerInterface.
     * 
     * @example new MyLogger(config) 
     * 
     * @return LoggerInterface|null Return preferred logger instance or null to use default logger.
    */
    public static function getLogger(): LoggerInterface|null 
    {
        return null;
    }
}