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
     * IP address API provider.
     * Available options: 'ipapi', 'iphub'.
     *
     * @var string $apiProvider ['ipapi', 'iphub']
     */
    public string $apiProvider = 'iphub';

    /**
     * IPHub API version (Applicable only when using 'iphub').
     *
     * @var string $ipHubVersion
     */
    public string $ipHubVersion = 'v2';

    /**
     * API key for the selected IP address provider.
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