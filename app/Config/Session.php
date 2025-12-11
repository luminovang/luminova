<?php 
/**
 * Luminova Framework backend session configuration.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

use Luminova\Base\Configuration;

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
     * > Avoid dynamically generating this value.
     */
    public string $namespace = 'user_session';

    /**
     * The directory where session files are stored on the server.
     * This does not affect the session cookie sent to the client.
     *
     * @var string $savePath
     */
    public string $savePath = ''; 

    /**
     * The session lifetime in seconds.
     *
     * @var int $expiration
     */
    public int $expiration = 365 * 24 * 60 * 60; 

    /**
     * The path on the server where the session cookie is accessible.
     * It determines the URL path under which the cookie is available.
     *
     * @var string $sessionPath
     */
    public string $sessionPath = '/'; 

    /**
     * The domain for which the session cookie is valid.
     *
     * @var string $sessionDomain
     */
    public string $sessionDomain = '.localhost';

    /**
     * The SameSite policy for session cookies.
     * Available options: 'None', 'Lax', 'Strict'.
     *
     * @var string $sameSite
     */
    public string $sameSite = 'Lax';

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
     * Storage method for CSRF tokens.
     * Available options: 'cookie', 'session'.
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