<?php 
/**
 * Luminova Framework backend session configuration.
 *
 * Controls how sessions are named, stored, secured, and expired.
 *
 * @package   Luminova
 * @author     Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license   See LICENSE file
 */
namespace App\Config;

use \Luminova\Base\Configuration;

final class Session extends Configuration
{
    /**
     * Name of the session cookie sent to the browser.
     * 
     * - Default is usually 'PHPSESSID'.
     * - You can rename it for security or branding.
     *
     * @var string $cookieName
     */
    public string $cookieName = 'PHPSESSID'; 

    /**
     * Identifier used to store session data on the server.
     * 
     * - Should be a fixed, unique string (e.g., an MD5 hash).
     * - Do not generate dynamically, or sessions may break.
     *
     * @var string $tableIndex
     */
    public string $tableIndex = '__server_side_session_table';

    /**
     * Directory path where session files are stored (server-side).
     * 
     * - If empty, PHP’s default session save path will be used.
     * - This does not affect the cookie sent to the client.
     *
     * @var string $savePath
     */
    public string $savePath = ''; 

    /**
     * Session lifetime in seconds.
     * 
     * - After this period, sessions will expire.
     * - Example: 365 days → 365 * 24 * 60 * 60.
     *
     * @var int $expiration
     */
    public int $expiration = 365 * 24 * 60 * 60; 

    /**
     * Path under which the session cookie is valid.
     * 
     * - '/' means the cookie is available site-wide.
     * - You can limit it to a subdirectory if needed.
     *
     * @var string $sessionPath
     */
    public string $sessionPath = '/'; 

    /**
     * Domain where the session cookie is valid.
     * 
     * - Example: '.example.com' will work on all subdomains.
     * - For local development: '.localhost' is typical.
     *
     * @var string $sessionDomain
     */
    public string $sessionDomain = '.localhost';

    /**
     * SameSite policy for session cookies.
     * 
     * - Options: 'None', 'Lax', 'Strict'.
     * - 'None' requires cookies to be sent over HTTPS (Secure flag).
     *
     * @var string $sameSite
     */
    public string $sameSite = 'Lax';

    /**
     * Whether to tie sessions to a fixed IP address.
     * 
     * - If true, sessions are invalidated when the user’s IP changes.
     * - Useful for high-security apps, but may affect mobile users.
     *
     * @var bool $strictSessionIp
     */
    public bool $strictSessionIp = false;

    /**
     * Whether to encrypt session data stored in cookies.
     * 
     * - If enabled, data is encrypted using your app’s encryption settings.
     * - Recommended if you store sensitive data client-side.
     *
     * @var bool $encryptCookieData
     */
    public bool $encryptCookieData = false;

    /**
     * Storage method for CSRF tokens.
     * 
     * - 'cookie': token stored in a cookie.
     * - 'session': token stored in session data.
     *
     * @var string $csrfStorage
     * @see https://luminova.ng/docs/0.0.0/security/csrf
     */
    public string $csrfStorage = 'session';
}