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

/**
 * This IPConfig class is responsible 
 * for retrieving client ip address information
 * Using a third party api Service like https://ipapi.co or https://iphub.info
*/
final class IPConfig
{
    /**
     * Specify the IP address information API provide 
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
 }