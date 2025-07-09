<?php 
/**
 * Luminova Framework API configuration.
 *
 * @package   Luminova
 * @author    Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license   See LICENSE file
 */
namespace App\Config;

use \Luminova\Base\Configuration;

final class Apis extends Configuration
{
    /**
     * Whether to reject API requests with an empty Origin header.
     * 
     * If true, requests without an Origin header are denied.
     *
     * @var bool $forbidEmptyOrigin
     */
    public bool $forbidEmptyOrigin = false;

    /**
     * Whether to include credentials in API responses.
     * 
     * If true, the `Access-Control-Allow-Credentials` header is sent.
     *
     * @var bool $allowCredentials
     */
    public bool $allowCredentials = true;

    /**
     * Allowed origins for API requests.
     * 
     * - Use `'*'` to allow any origin.  
     * - Use the string `'null'` to also allow all origins explicitly.  
     * - Provide an array to restrict to specific origins.  
     *
     * @var array<int,string>|string $allowOrigins
     */
    public array|string $allowOrigins = '*';

    /**
     * Allowed request headers for API requests.
     * 
     * An empty array allows all headers by default.  
     *
     * @var array<int,string> $allowHeaders
     */
    public array $allowHeaders = [
        // 'Content-Type',
        // 'Authorization',
        // 'X-Requested-With',
        // 'Host',
        // 'Accept', 
        // 'User-Agent'
    ];
}