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

use \Luminova\Config\Configuration;

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
     * Define where HTTP CORS security policies are enforced.
     *
     * Controls whether CORS validation rules are applied to all requests,
     * only web requests, only API requests, a specific URI prefix, or disabled completely.
     *
     * CORS validation includes:
     * - `forbidEmptyOrigin`
     * - `allowCredentials`
     * - `allowOrigins`
     * - `allowHeaders`
     *
     * Modes:
     * - `all`  - Enforce CORS security on all HTTP requests.
     * - `web`  - Enforce CORS security only on non-API requests.
     * - `api`  - Enforce CORS security only on API requests determined by
     *            {@see env('app.api.prefix')} or {@see Luminova::isApiPrefix()}.
     * - `none` - Disable CORS security validation.
     * Or a custom URI prefix to enforce CORS security only (e.g, `users`)
     *
     * @var string $httpCorsPolicyMode
     */
    public static string $httpCorsPolicyMode = 'api';

    /**
     * Reject API requests with empty Origin headers.
     *
     * If true, any request without an Origin header will be forbidden.
     *
     * @var bool $forbidEmptyOrigin
     */
    public static bool $forbidEmptyOrigin = false;

    /**
     * Allow credentials in API requests.
     *
     * If true, the `Access-Control-Allow-Credentials` header will be sent
     * in CORS responses.
     *
     * @var bool $allowCredentials
     */
    public static bool $allowCredentials = true;

    /**
     * Allowed origins for CORS requests.
     *
     * Defines which request origins are permitted. Supports:
     * - `'*'`    - Allow requests from any origin. The browser will receive `*`
     *             as the allowed origin when credentials are disabled.
     * - `'null'` - Allow requests with a `null` origin (for example, local files
     *             or sandboxed documents).
     * - `'self'` - Allow only the application origin defined by
     *             {@see APP_HOSTNAME} or {@see env('app.hostname')}.
     * - A string containing a single allowed origin.
     * - An array containing multiple allowed origins.
     *
     * Origins may include:
     * - A hostname: `example.com`
     * - A subdomain: `api.example.com`
     * - A wildcard subdomain: `.website.com` (allows `website.com` and its subdomains)
     * - A custom port: `api.example.com:8443`
     *
     * @var string|string[] $allowOrigins
     *
     * @example - Example:
     * ```php
     * $allowOrigins = [
     *     'example.com',          // Allow only the main domain
     *     'api.example.com',      // Allow only a specific subdomain
     *     '.website.com',         // Allow the domain and all subdomains
     *     'api.example.com:8443'  // Allow an origin with a custom port
     * ];
     * ```
     */
    public static array|string $allowOrigins = '*';

    /**
     * Allowed headers for API requests.
     *
     * Defines the headers that will be accepted in the
     * `Access-Control-Allow-Headers` response. An empty array allows all headers.
     *
     * @var string[] $allowHeaders
     * @example - Example:
     * ```php
     * $allowHeaders = [
     *      'Content-Type',
     *      'Authorization',
     *      'X-Requested-With',
     *      'Host',
     *      'Accept', 
     *      'User-Agent'
     * ];
     * ```
     */
    public static array $allowHeaders = [ ];

    /**
     * Response headers exposed to browser clients in cross-origin requests.
     *
     * Browsers hide non-standard response headers from JavaScript unless they
     * are explicitly listed in `Access-Control-Expose-Headers`.
     *
     * @var string[] $exposeHeaders
     *
     * @example - Example:
     * ```php
     * $exposeHeaders = [
     *     'X-Request-ID',
     *     'X-RateLimit-Remaining'
     * ];
     * ```
     */
    public static array $exposeHeaders = [];

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
    public static array $trustedOrigins = [];

    /**
     * Trusted hostnames or patterns.
     *
     * Requests from these hostnames are considered safe.
     *
     * @see Luminova\Http\Request::isTrusted()
     *
     * @var array<int,string> $trustedHostnames
     * 
     * @example - Example:
     * ```php
     * [
     *      'example.com', 
     *      '*.trusted.net'
     * ]
     * ```
     */
    public static array $trustedHostnames = [];

    /**
     * Apply API-style security to all HTTP requests.
     *
     * When enabled, all incoming HTTP requests are validated using the same
     * CORS security rules as API requests.
     *
     * @deprecated Use {@see $httpCorsPolicyMode} with the `all` mode instead.
     *
     * @var bool $enforceApiSecurityOnHttp
     */
    public bool $enforceApiSecurityOnHttp = false;
}