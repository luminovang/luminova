<?php
declare(strict_types=1);
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

use \Luminova\Config\DatabaseConfig;

class Database extends DatabaseConfig
{
    /**
     * {@inheritDoc}
     */
    public static bool $connectionSharding = false;
    
    /**
     * {@inheritDoc}
     *
     * @example - Example:
     * ```php
     * public static bool $shardFallbackOnError = true;
     * ```
     */
    public static bool $shardFallbackOnError = false;

    /**
     * {@inheritDoc}
     *
     * @example - Example:
     * ```php
     * protected static array $databaseServers = [
     *     'NG' => [
     *         'host' => 'ng.db.server',
     *         'port' => 3306,
     *         'database' => 'my_db_ng',
     *         'connection' => 'pdo',
     *         'username' => 'user_ng',
     *         'password' => 'secret',
     *         'charset' => 'utf8mb4',
     *         'pdo_version' => 'mysql',
     *         'production' => false,
     *         'persistent' => true,
     *         'emulate_prepares' => true,
     *         'timeout' => 0,
     *         'socket' => false,
     *         'socket_path' => null,
     *         'sqlite_path' => null, // Only used if PDO version is sqlite
     *     ],
     *     'US' => [
     *         'host' => 'us.db.server',
     *         ...
     *     ]
     * ];
     * ```
     */
    protected static array $databaseServers = [];

    /**
     * {@inheritDoc}
     *
     * @example - Static shard mapping:
     * ```php
     * public static function getShardServerIdentifier(): ?string 
     * {
     *     return 'NG';
     * }
     * ```
     *
     * @example - Dynamic shard resolution:
     * ```php
     * public static function getShardServerIdentifier(): ?string 
     * {
     *     return resolveUserRegion();
     * }
     * ```
     */
    public static function getShardServerIdentifier(): string|int|null
    {
        return null;
    }
}