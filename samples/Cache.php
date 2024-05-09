<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Utils;

use \Luminova\Cache\FileCache;

class Cache
{
    public static string $path = '/writeable/caches/database/';

    public static function deleteFile(string $storage): void
    {
        FileCache::deleteDisk(static::$path, $storage);
    }

    public static function deleteFiles(array $storages): void
    {
        foreach ($storages as $storage) {
            FileCache::deleteDisk(static::$path, $storage);
        }
    }
}
