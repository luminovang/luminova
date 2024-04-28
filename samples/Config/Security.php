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

final class Security
{
    /**
     * List of trusted origin domains or patterns.
     * 
     * @var array<int, string> $trustedOrigins
    */
    public static array $trustedOrigins = [];

    /**
     * List of trusted hostname or patterns.
     * 
     * @var array<int, string> $trustedHostname
    */
    public static array $trustedHostname = [];
 }