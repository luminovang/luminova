<?php 
/**
 * Luminova Framework HTTP request security configuration.
 *
 * @package   Luminova
 * @autor     Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license   See LICENSE file
 */
namespace App\Config;

use \Luminova\Base\Configuration;

final class Security extends Configuration
{
    /**
     * List of trusted origin domains or wildcard patterns.
     * 
     * These origins are allowed to make cross-origin (CORS) requests to your app.  
     * Use full URLs or wildcards if needed.  
     * 
     * Examples: 
     * - ['https://example.com', '*.trusted.com']
     *
     * @var array<int,string> $trustedOrigins
     */
    public array $trustedOrigins = [];

    /**
     * List of trusted hostnames or wildcard patterns.
     * 
     * These hostnames are considered valid when the application handles requests.  
     * Use plain domain names or wildcards as needed.  
     * 
     * Example:
     * - ['example.com', '*.trusted.net']
     *
     * @var array<int,string> $trustedHostnames
     */
    public array $trustedHostnames = [];
}