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

final class IPConfig extends BaseConfig
{
    /**
     * Specify the IP address api provide 
     * Available are ipapi and iphub
     * 
     * @var string $apiProvider ['ipapi', 'iphub']
    */
    public string $apiProvider = 'iphub';

    /**
     * Specify the IP hub api version 
     * This is valid for iphub only 
     * 
     * @var string $ipHubVersion
    */
    public string $ipHubVersion = 'v2';

    /**
     * Specify the IP address api key
     * 
     * @var string $apiKey
    */
    public string $apiKey = '';

    /**
     * List of trusted proxy IP addresses or subnets.
     * 
     * @var array<int, string> $trustedProxies
    */
    public array $trustedProxies = [];
 }