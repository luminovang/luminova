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

use \Luminova\Base\Configuration;

final class Encryption extends Configuration
{
    /**
     * Which encryption engine to use.
     * 
     * - 'openssl' (default) — widely available and reliable.
     * - 'sodium' — newer, built-in in PHP 7.2+, often faster and safer by default.
     * 
     * This controls which library handles all encryption and decryption.
     *
     * @var string $handler
     */
    public string $handler = 'openssl';

    /**
     * Sodium cipher algorithm selector.
     *
     * Determines which libsodium construction is used:
     * - 'secretbox' → sodium_crypto_secretbox_* (simple symmetric encryption)
     * - 'aead'      → sodium_crypto_aead_xchacha20poly1305_ietf_* (supports associated data)
     *
     * This value controls encryption. Decryption should rely on the
     * algorithm stored in the payload, not this config.
     *
     * @var string $sodiumCipher (`Sodium::SECRETBOX` or `Sodium::AEAD`)
     */
    public string $sodiumCipher = 'aead';

    /**
     * The specific encryption algorithm when using OpenSSL.
     * 
     * Examples: 'AES-128-CBC', 'AES-192-CBC', 'AES-128-CFB', 'AES-128-ECB', 'AES-256-GCM'.
     * 
     * If you don't know which to pick, stick with 'AES-128-CBC' — it’s secure 
     * and widely supported. This setting is ignored if you use 'sodium'.
     *
     * @var string $method
     */
    public string $method = 'AES-128-CBC';
  
    /**
     * Hashing algorithm used to verify encrypted data hasn’t been changed.
     * 
     * Examples: 'SHA512', 'SHA256'.
     * 
     * SHA512 gives a longer hash (more collision-resistant), while SHA256 is 
     * slightly faster. Either is secure for most uses.
     *
     * @var string $digest
     */
    public string $digest = 'SHA256';

    /**
     * Extra key information or salt for OpenSSL.
     * 
     * This value is mixed into the encryption key to make it harder to guess.
     * Leave it empty unless you know you need to customize it.
     *
     * @var string $keyInfo
     */
    public string $keyInfo = '';
}