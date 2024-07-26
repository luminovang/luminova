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

use \Luminova\Base\BaseDatabase;


class Database extends BaseDatabase
{
    /**
     * Optional servers to connect to when main server fails
     * An associative array with each database configuration keys and values.
     * 
     * Supported array keys 
     * @example $databaseBackups = [
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
     * @var array $databaseBackups
    */
    protected static array $databaseBackups = [];
}