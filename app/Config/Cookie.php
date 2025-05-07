<?php 
/**
 * Luminova Framework client related cookie configuration.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

use \Luminova\Base\BaseConfig;

final class Cookie extends BaseConfig
{
    /**
     * The directory where cookie-related data may be stored on the server.
     *
     * @var string $savePath
     */
    public string $savePath = ''; 

    /**
     * The cookie's expiration time in seconds.
     * This determines how long the cookie remains valid.
     *
     * @var int $expiration
     */
    public int $expiration = 365 * 24 * 60 * 60; 

    /**
     * The path where the cookie is accessible.
     * This restricts the availability of the cookie to a specific directory.
     * 
     * @var string $cookiePath (e.g., `'/my-project/'`)
     */
    public string $cookiePath = '/';

    /**
     * The domain for which the cookie is valid.
     * This determines which domains can access the cookie.
     * 
     * @var string $cookieDomain (e.g., `'.' . APP_HOSTNAME`)
     */
    public string $cookieDomain = '.localhost';

    /**
     * The SameSite policy for cookies.
     * Defines how cookies are sent with cross-site requests.
     * Available options: 'None', 'Lax', 'Strict'.
     *
     * @var string $sameSite
     */
    public string $sameSite = 'Lax';

    /**
     * Enforce secure cookies by requiring an HTTPS connection.
     * If enabled, cookies will not be sent over an insecure HTTP connection.
     *
     * @var bool $secure
     */
    public bool $secure = false;

    /**
     * Restrict cookie access to HTTP(S) requests only.
     * If enabled, JavaScript cannot access the cookie via `document.cookie`.
     *
     * @var bool $httpOnly
     */
    public bool $httpOnly = true;

    /**
     * Enable setting raw (non-URL-encoded) cookies.
     * If enabled, cookie names and values must comply with RFC 2616.
     *
     * @var bool $cookieRaw
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#attributes
     * @see https://datatracker.ietf.org/doc/html/rfc2616#section-2.2
     */
    public bool $cookieRaw = false;
}