<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App;

use \Psr\Log\LoggerInterface;
use \Psr\Http\Client\ClientInterface;
use \Luminova\Base\Cache as CacheProvider;
use \Luminova\Foundation\Core\Application;
use \Luminova\Interface\{
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
    public static function shouldShareObject(): bool
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
     */
    public function getCacheProvider(mixed ...$arguments): CacheProvider|string|null
    {
        return null;
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