<?php
declare(strict_types=1);
/**
 * Luminova framework session configuration.
 *
 * Defines session storage, cookie, expiration, and security settings.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

use Luminova\Config\Configuration;

final class Session extends Configuration
{
    /**
     * The name of the session cookie.
     *
     * @var string $cookieName
     */
    public string $cookieName = 'PHPSESSID'; 

    /**
     * The default storage namespace for user session data.
     * 
     * A fixed unique identifier (e.g., an MD5 hash string).
     *
     * @var string $namespace
     * 
     * > Avoid dynamically generating this value.
     */
    public string $namespace = 'user_session';

    /**
     * Directory used to store session files on the server.
     *
     * Set to `null` or an empty string (`""`) to use the default PHP session
     * save path configured in `php.ini`.
     *
     * This setting does not affect the session cookie or its storage on the client.
     *
     * @var string|null $savePath
     */
    public ?string $savePath = '';

    /**
     * Session cookie lifetime in seconds.
     *
     * Determines how long the browser retains the session cookie.
     * Set to `0` to expire the cookie when the browser session ends.
     *
     * @var int $expiration (default: 30 days).
     */
    public int $expiration = 30 * 24 * 60 * 60;

    /**
     * Maximum session data lifetime in seconds before becoming eligible
     * for garbage collection.
     *
     * @var int $gcMaxLifetime (default: 30 days).
     */
    public int $gcMaxLifetime = 30 * 24 * 60 * 60;

    /**
     * URL path where the session cookie is accessible.
     *
     * @var string $sessionPath
     */
    public string $sessionPath = '/';

    /**
     * Domain for which the session cookie is valid.
     *
     * Set to an empty string (`""`) to use a host-only cookie.
     *
     * @var string $sessionDomain
     */
    public string $sessionDomain = '.localhost';

    /**
     * SameSite policy for the session cookie.
     *
     * Allowed values: `Lax`, `Strict`, `None`.
     * `None` requires the `Secure` cookie attribute.
     *
     * @var string $sameSite
     */
    public string $sameSite = 'Lax';

    /**
     * Cache limiter used when starting a session.
     *
     * Controls the HTTP cache headers PHP sends to the client when a session
     * starts. This determines how browsers and intermediate caches handle
     * responses containing session data.
     *
     * Supported values:
     * - `nocache`: Prevents caching by sending headers that disable browser and
     *   proxy caching. This is the default and recommended option for sessions
     *   containing sensitive data.
     * - `private`: Allows caching by the user's browser but prevents shared
     *   caches from storing the response.
     * - `private_no_expire`: Same as `private`, but does not send an expiration
     *   header.
     * - `public`: Allows both private and shared caches to store the response.
     * - `''`: Disables PHP's automatic cache-control headers.
     *
     * @var string $cacheLimiter
     */
    public string $cacheLimiter = 'nocache';

    /**
     * Enable strict session IP authentication.
     * 
     * If enabled, the session will be invalidated if the user's IP address changes.
     *
     * @var bool $strictSessionIp
     */
    public bool $strictSessionIp = true;

    /**
     * Whether to enable strict session ID validation.
     *
     * When enabled, PHP rejects session IDs that do not already exist in the
     * configured session storage and generates a new session ID instead.
     *
     * @var bool $useStrictMode
     * @see https://www.php.net/manual/en/session.configuration.php#ini.session.use-strict-mode
     */
    public bool $useStrictMode = true;

    /**
     * Enable encryption for session data stored in cookies.
     * If enabled, the data will be encrypted using the application's encryption key and algorithm.
     *
     * @var bool $encryptCookieData
     */
    public bool $encryptCookieData = false;

    /**
     * Storage method used to persist CSRF tokens.
     *
     * Supported values: `session`, `cookie`.
     *
     * @var string $csrfStorage
     */
    public string $csrfStorage = 'session';

    /**
     * {@inheritDoc}
     */
    protected function onCreate(): void 
    {
        if(PRODUCTION){
            $this->sessionDomain = '.' . APP_HOSTNAME;
        }
    }
}