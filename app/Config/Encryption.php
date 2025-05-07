<?php 
/**
 * Luminova Framework application encryption configuration.
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
     * Encryption handler to use.
     * Available options: 'openssl', 'sodium'.
     *
     * @var string $handler
     */
    public string $handler = 'openssl';

    /**
     * Encryption method for OpenSSL.
     * Examples: 'AES-128-CBC', 'AES-192-CBC', 'AES-128-CFB', 'AES-128-ECB'.
     *
     * @var string $method
     */
    public string $method = 'AES-128-CBC';
  
    /**
     * Hashing algorithm for data integrity.
     * Examples: 'SHA512', 'SHA256'.
     *
     * @var string $digest
     */
    public string $digest = 'SHA512';

    /**
     * OpenSSL key information.
     *
     * @var string $keyInfo
     */
    public string $keyInfo = '';
}