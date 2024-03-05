<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

namespace App\Controllers\Config;

final class Session 
{
    /**
     * The name of the session cookie.
     * 
     * @var string $cookieName;
     */
    public static string $cookieName = "PHPSESSID"; 

    /**
     * The path where session files are stored on the server.
     * 
     * @var string $savePath;
     */
    public static string $savePath = ""; 

    /**
     * The lifetime of the session in seconds.
     * 
     * @var int $expiration;
     */
    public static int $expiration = 365 * 24 * 60 * 60; 

    /**
     * The path to use for the session cookie.
     * 
     * @var string $sessionPath;
     */
    public static string $sessionPath = "/"; 

    /**
     * The domain to use for the session cookie.
     * 
     * @var string $sessionDomain;
     */
    public static string $sessionDomain = ".localhost";

     /**
     * Set the session cookie security level.
     * None, Lax, Strict 
     * 
     * @var string $sameSite;
     */
    public static string $sameSite = "Lax";

     /**
     * Enable strict session IP authentication.
     * If set to true, the user will be logged out if their IP address changes.
     * 
     * @var bool $strictSessionIp
     */
    public static bool $strictSessionIp = false;

    /**
     * Set the csrf storage engine 
     * cookie, session, 
     * 
     * @var string $csrfStorage;
     */
    public static string $csrfStorage = 'session';
}
