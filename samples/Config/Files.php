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

final class Files
{
    /**
     * Unix file permissions.
     * 
     * @var int $filePermissions 
    */
    public static int $filePermissions = 0644;

    /**
     * Unix directory permissions.
     * 
     * @var int $dirPermissions 
    */
    public static int $dirPermissions = 0755;
}