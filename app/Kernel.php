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
namespace App;

use \Redis;
use \Memcached;
use \Psr\Log\LoggerInterface;
use \Psr\Http\Client\ClientInterface;
use Luminova\Base\Cache as BaseCache;
use Luminova\Foundation\Core\Application;
use Luminova\Cache\{RedisCache, FileCache, MemoryCache};
use Luminova\Interface\{
    RouterInterface, 
    MailerInterface, 
    SessionInterface,
    ServiceKernelInterface
};

/**
 * {@inheritDoc}
 */
final class Kernel implements ServiceKernelInterface
{
    /**
     * The singleton instance of the kernel.
     *
     * @var ServiceKernelInterface|null $instance
     */
    private static ?ServiceKernelInterface $instance = null;

    /**
     * {@inheritDoc}
     * 
     * @see \Luminova\Luminova::kernel() for easy usage.
     */
    public static function create(bool $shared): static
    {
        if (!$shared) {
            return new self();
        }

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * {@inheritDoc}
     */
    public static function shouldShareObject(?string $id = null): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed ...$arguments): mixed
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutingSystem(Application $app): ?RouterInterface
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getApplication(): ?Application
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getLogger(mixed ...$arguments): ?LoggerInterface
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMailer(mixed ...$arguments): MailerInterface|string|null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @example Basic usage:
     * ```php
     * $cache = Luminova::kernel()->getCacheProvider('redis');
     * ```
     *
     * @example With storage and persistence:
     * ```php
     * $cache = Luminova::kernel()->getCacheProvider(
     *     'redis',
     *     'storage',
     *     'persistent-id',
     *     'global'
     * );
     * ```
     * 
     * @example Save as Above:
     * ```php
     * $cache = Luminova::kernel(
     *      service: 'cache'
     *      shared: true,
     *      // getCacheProvider params
     *      'redis',        // the cache driver param
     *      'storage',      // the cache storage param
     *      'persistent-id', // persistentId param
     *      'context'       // context param
     * )
     * ```
     *
     * @note
     * If no underlying connection is provided, the driver will resolve:
     * - Memcached from {@see self::getMemcached()}
     * - Redis from {@see self::getRedis()}
     */
    public function getCacheProvider(
        string $driver,
        ?string $storage = null,
        ?string $persistentId = null,
        string $context = 'global'
    ): ?BaseCache
    {
        $cache = match($driver){
            'redis'      => new RedisCache(storage: $storage, persistentId: $persistentId),
            'filecache'  => new FileCache(storage: $storage, persistentId: $persistentId),
            'memcached'  => new MemoryCache(storage: $storage, persistentId: $persistentId),
            default      => null
        };

        // Option cache configuration
        // $cache->configureGarbageCollection(true);
        $cache->setSerializerOption(BaseCache::SERIALIZER_PHP_AUTO, false);

        return $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function getMemcached(
        ?string $persistentId = null, 
        ?callable $onNewObject = null, 
        string $identifier = ''
    ): ?Memcached
    {
        $mem = new Memcached($persistentId ?? '', $onNewObject, $identifier);
        $mem->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

        if (($prefix = env('memcached.key.prefix')) !== null) {
            $mem->setOption(Memcached::OPT_PREFIX_KEY, $prefix);
        }

        $mem->addServer('127.0.0.1', 11211, 0);

        return $mem;
    }

    /**
     * {@inheritDoc}
     */
    public function getRedis(
        ?string $persistentId = null,
        ?callable $onNewObject = null,
        string $identifier = ''
    ): ?Redis
    {
        $redis = new Redis();
        $redis->connect(
            env('redis.host', '127.0.0.1'),
            env('redis.port', 6379),
            env('redis.timeout', 0)
        );

        if (($password = env('redis.password')) !== null) {
            $redis->auth($password);
        }

        $redis->select((int) env('redis.database', 0));

        if (($prefix = env('redis.key.prefix')) !== null) {
            $redis->setOption(Redis::OPT_PREFIX, $prefix);
        }

        if($onNewObject !== null){
            $onNewObject($redis);
        }

        return $redis;
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionClient(mixed ...$arguments): SessionInterface|string|null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getHttpClient(mixed ...$arguments): ClientInterface|string|null
    {
        return null;
    }
}