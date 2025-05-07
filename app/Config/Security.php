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

final class Security extends BaseConfig
{
    /**
     * List of trusted origin domains or wildcard patterns.
     * These origins are allowed for cross-origin requests.
     * 
     * Examples: `['https://example.com', '*.trusted.com']`
     * 
     * @var array<int,string> $trustedOrigins
     */
    public array $trustedOrigins = [];

    /**
     * List of trusted hostname's or wildcard patterns.
     * These hostname's are considered valid for application requests.
     * 
     * Example:`['example.com', '*.trusted.net']`
     * 
     * @var array<int,string> $trustedHostnames
     */
    public array $trustedHostnames = [];
}