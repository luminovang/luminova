<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

namespace App\Config;

use \Luminova\Base\BaseConfig;

final class Apis extends BaseConfig
{
    /**
     * Indicates whether to forbid requests with empty request origin headers.
     * 
     * @var bool $forbidEmptyOrigin If true, requests with empty Origin headers are rejected.
     */
    public bool $forbidEmptyOrigin = false;

    /**
     * Indicates whether to allow credentials in API requests.
     * 
     * @var bool $allowCredentials If true, the Access-Control-Allow-Credentials header is sent.
     */
    public bool $allowCredentials = true;

    /**
     * Specifies allowed origins for the Access-Control-Allow-Origin header in API requests.
     * 
     * A wildcard `*` allows requests from any origin. 
     * Passing the string `null` also permits all origins.
     * 
     * @var array<int,string>|string $allowOrigins An array of allowed origins or a wildcard `*`.
     */
    public array|string $allowOrigins = '*';

    /**
     * Defines the allowed headers for the Access-Control-Allow-Headers header in API requests.
     * 
     * @var array<int,string> $allowHeaders An array of allowed header names or an empty array to allow all headers.
     */
    public array $allowHeaders = [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Host',
        'Accept', 
        'User-Agent'
    ];
}