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

final class Security extends BaseConfig
{
    /**
     * List of trusted origin domains or patterns.
     * 
     * @var array<int, string> $trustedOrigins
    */
    public array $trustedOrigins = [];

    /**
     * List of trusted hostname or patterns.
     * 
     * @var array<int, string> $trustedHostname
    */
    public array $trustedHostname = [];
 }