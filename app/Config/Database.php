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

use \Luminova\Core\CoreDatabase;

class Database extends CoreDatabase
{
    /**
     * Enable or disable connection-level database sharding.
     *
     * When set to true, the application will attempt to route database connections 
     * based on shard configurations defined by the shard location identifier `getShardServerKey`. 
     * This allows for distributing database load across multiple shard servers 
     * for better scalability and isolation (e.g., multi-tenant or geo-based setups).
     *
     * If false, all connections will be made to the default primary database configuration.
     *
     * @var bool $connectionSharding
     */
    public static bool $connectionSharding = false;
    
    /**
     * Whether to fallback to an available backup server if the selected shard is unreachable.
     * 
     * When enabled, the system will attempt to use a backup configuration from `$databaseServers`
     * if the main shard server fails or is offline.
     * 
     * This is useful in distributed environments where high availability is required.
     *
     * @var bool $shardFallbackOnError
     *
     * @example
     * Database::$shardFallbackOnError = true;
     */
    public static bool $shardFallbackOnError = false;

    /**
     * List of backup database configurations.
     * 
     * This serves two purposes:
     * - Provides alternative connections for **sharded environments** when a shard is unavailable.
     * - Acts as a **failover mechanism** for non-sharded environments when the main database fails.
     * 
     * Each backup should follow the same structure as the primary configuration.
     *
     * @var array<string, array<string, mixed>> $databaseServers
     *
     * @example
     * Database::$databaseServers = [
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
     */
    protected static array $databaseServers = [];

    /**
     * Get the target shard location identifier.
     * 
     * This method determines the shard key used to route the database connection.
     * It supports static values (e.g., a region code like 'NG') or can be overridden
     * to return the appropriate shard dynamically based on runtime conditions.
     *
     * Typical use cases include:
     * - Geo-based sharding
     * - Load-balanced regional databases
     * - Multi-tenant database separation
     *
     * @return string Return the shard identifier key (e.g., 'NG', 'EU').
     *
     * @example Static shard mapping:
     * ```php
     * public static function getShardServerKey(): ?string 
     * {
     *     return 'NG';
     * }
     * ```
     *
     * @example Dynamic shard resolution:
     * ```php
     * public static function getShardServerKey(): ?string 
     * {
     *     return resolveUserRegion();
     * }
     * ```
     */
    public static function getShardServerKey(): string
    {
        return '';
    }
}