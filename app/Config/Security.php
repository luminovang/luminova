<?php 
/**
 * HTTP Request Security Configuration for Luminova Framework.
 *
 * This configuration class manages security settings for incoming HTTP requests,
 * including CORS handling, origin validation, and trusted hosts.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

use \Luminova\Base\Configuration;

final class Security extends Configuration
{
    /**
     * Default permissions for newly created files (Unix-style).
     * 
     * - **0644** → The file owner can read/write, everyone else can only read.  
     * - Good for most applications — it prevents others from modifying your files.
     *
     * @var int $filePermissions Default: `0644`
     */
    public static int $filePermissions = 0644;

    /**
     * Default permissions for newly created directories (Unix-style).
     * 
     * - **0755** → The directory owner can read/write/execute, 
     *   everyone else can only read/execute.  
     * - This is safe and allows the web server to read files inside while 
     *   preventing unauthorized modifications.
     *
     * @var int $dirPermissions Default: `0755`
     */
    public static int $dirPermissions = 0755;

    /**
     * Apply API-style security to all HTTP requests.
     *
     * If set to `true`, all incoming HTTP requests (not just API requests)
     * will be validated using the same security rules as API requests, including:
     * - `forbidEmptyOrigin`
     * - `allowCredentials`
     * - `allowOrigins`
     * - `allowHeaders`
     *
     * This is useful when you want consistent security enforcement across
     * web pages, AJAX calls, and API endpoints.
     *
     * @var bool $enforceApiSecurityOnHttp
     */
    public bool $enforceApiSecurityOnHttp = false;

    /**
     * Reject API requests with empty Origin headers.
     *
     * If true, any request without an Origin header will be forbidden.
     *
     * @var bool $forbidEmptyOrigin
     */
    public bool $forbidEmptyOrigin = false;

    /**
     * Allow credentials in API requests.
     *
     * If true, the `Access-Control-Allow-Credentials` header will be sent
     * in CORS responses.
     *
     * @var bool $allowCredentials
     */
    public bool $allowCredentials = true;

    /**
     * Allowed origins for API requests.
     *
     * Can be:
     * - `'*'` to allow requests from any origin
     * - `'null'` to allow all origins
     * - An array of specific origin URLs
     *
     * @var array<int,string>|string $allowOrigins
     */
    public array|string $allowOrigins = '*';

    /**
     * Allowed headers for API requests.
     *
     * Defines the headers that will be accepted in the
     * `Access-Control-Allow-Headers` response. An empty array allows all headers.
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

    /**
     * Trusted origin domains or patterns.
     *
     * Requests from these origins are considered safe.
     *
     * @see Luminova\Http\Request::isTrusted()
     * @see Luminova\Http\Request::isTrustedOrigin()
     *
     * @var array<int,string> $trustedOrigins
     * 
     * @example - Example:
     * ```php
     * [
     *      'https://example.com', 
     *      '*.trusted.com'
     * ]
     * ```
     */
    public array $trustedOrigins = [];

    /**
     * Trusted hostnames or patterns.
     *
     * Requests from these hostnames are considered safe.
     *
     * @see Luminova\Http\Request::isTrusted()
     *
     * @var array<int,string> $trustedHostname
     * 
     * @example - Example:
     * ```php
     * [
     *      'example.com', 
     *      '*.trusted.net'
     * ]
     * ```
     */
    public array $trustedHostname = [];
}