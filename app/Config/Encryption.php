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

final class Encryption extends BaseConfig
{
    /**
     * Specify encryption algorithm to use (openssl or sodium).
     * 
     * @var string $handler
     */
    public string $handler = 'openssl';

    /**
     * The encryption mode for openssl (e.g., 'AES-128-CBC', 'AES-192-CBC', 'AES-128-CFB', 'AES-128-ECB').
     * 
     * @var string $mode
     */
    public string $method = 'AES-128-CBC';
  
    /**
     * Specify hashing digest to use (e.g., SHA512, SHA256).
     * 
     * @var string $digest
     */
    public string $digest = 'SHA512';

    /**
     * Openssl key Information,
     *
     * @var string $keyInfo
     */
    public string $keyInfo = '';
}