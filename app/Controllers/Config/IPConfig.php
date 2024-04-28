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

final class IPConfig
{
    /**
     * Specify the IP address api provide 
     * Available are ipapi and iphub
     * 
     * @var string $apiProvider ['ipapi', 'iphub']
    */
    public static string $apiProvider = 'iphub';

    /**
     * Specify the IP hub api version 
     * This is valid for iphub only 
     * 
     * @var string $ipHubVersion
    */
    public static string $ipHubVersion = 'v2';

    /**
     * Specify the IP address api key
     * 
     * @var string $apiKey
    */
    public static string $apiKey = '';

    /**
     * List of trusted proxy IP addresses or subnets.
     * 
     * @var array<int, string> $trustedProxies
    */
    public static array $trustedProxies = [];
 }