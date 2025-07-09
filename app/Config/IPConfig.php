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

use \Luminova\Base\Configuration;

final class IPConfig extends Configuration
{
    /**
     * Which IP lookup service to use.
     * 
     * - **'ipapi'** → Simple IP geolocation and info lookup.  
     * - **'iphub'** → Detects VPNs, proxies, and hosting providers.  
     * 
     * Choose based on whether you just need location data or want to block 
     * suspicious traffic.
     *
     * @var string $apiProvider ['ipapi', 'iphub']
     */
    public string $apiProvider = 'iphub';

    /**
     * Which API version to use for IPHub. 
     * 
     * This setting is only used if `$apiProvider` is set to 'iphub'.  
     * Use the latest version unless you need to support older integrations.
     *
     * @var string $ipHubVersion
     */
    public string $ipHubVersion = 'v2';

    /**
     * Your API key for the selected IP service provider.
     * 
     * - Get this key from the provider’s dashboard.  
     * - Without a valid key, requests may fail or be limited.  
     *
     * @var string $apiKey
     */
    public string $apiKey = '';

    /**
     * IP addresses or CIDR subnets you trust as proxies.
     * 
     * - Requests coming from these IPs will be treated as trusted.  
     * - Useful if your app is behind a load balancer or CDN.  
     * - Example: `['192.168.1.10', '10.0.0.0/8']`.  
     *
     * @var array<int,string> $trustedProxies
     */
    public array $trustedProxies = [];
}