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

final class Files extends BaseConfig
{
    /**
     * Unix file permissions.
     * 
     * @var int $filePermissions 
    */
    public int $filePermissions = 0644;

    /**
     * Unix directory permissions.
     * 
     * @var int $dirPermissions 
    */
    public int $dirPermissions = 0755;
}