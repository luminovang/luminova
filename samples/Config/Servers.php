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

use \Luminova\Base\BaseServers;


class Servers extends BaseServers
{
    /**
     * Optional servers to connect to when main server fails
     * An associative array with each database configuration keys and values.
     * 
     * Supported array keys 
     * @example $databaseServers = [
     *      [
     *          'port' => 0,
     *          'host' => '',
     *          'version' => 'mysql',
     *          'charset' => 'utf8',
     *          'sqlite_path' => '',
     *          'username' => 'root',
     *          'password' => '',
     *          'database' => 'db_name',
     *          'socket' => false,
     *          'socket_path' => ',
     *      ],
     *      ...
     * ]
     * 
     * @var array $databaseServers
    */
    protected static array $databaseServers = [];
}