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

final class Apis
{
    /**
     * Indicates whether to forbid requests with empty Origin headers.
     * 
     * @var bool $forbidEmptyOrigin If true, requests with empty Origin headers are forbidden.
    */
    public static bool $forbidEmptyOrigin = true;

    /**
     * Set whether to allow credentials in API requests.
     * 
     * @var bool $allowCredentials If true, the Access-Control-Allow-Credentials header is included.
     */
    public static bool $allowCredentials = true;

    /**
     * Set the allowed origins for the Access-Control-Allow-Origin header in API requests.
     * 
     * Wildcard `*` allows all origins.
     * 
     * @var array<int,string>|string $allowOrigins An array of allowed origins or a wildcard `*`.
     * 
     * > Note passing string null will result in allow all origins.
    */
    public static array|string $allowOrigins = [];

    /**
     * Set the allowed headers for the Access-Control-Allow-Headers header in API requests.
     * 
     * @var array<int,string> $allowHeaders An array of allowed header names.
     */
    public static array $allowHeaders = [
        'Content-Type',
        'Access-Control-Allow-Headers',
        'Authorization',
        'X-Requested-With'
    ];
}