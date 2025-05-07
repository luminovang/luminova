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

use \Luminova\Base\BaseConfig;

final class Session extends BaseConfig
{
    /**
     * The name of the session cookie.
     *
     * @var string $cookieName
     */
    public string $cookieName = 'PHPSESSID'; 

    /**
     * The default storage index for user session data.
     * This should be a unique and fixed identifier (e.g., an MD5 hash string).
     * Avoid dynamically generating this value.
     *
     * @var string $tableIndex
     */
    public string $tableIndex = '__server_side_session_table';

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
     * If enabled, the session will be invalidated if the user's IP address changes.
     *
     * @var bool $strictSessionIp
     */
    public bool $strictSessionIp = false;

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
}