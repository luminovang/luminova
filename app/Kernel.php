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

use Luminova\Foundation\Core\ServiceKernel;

/**
 * Application service kernel.
 * 
 * {@inheritDoc}
 * 
 * @link https://luminova.ng/docs/0.0.0/foundation/service-kernel
 */
final class Kernel extends ServiceKernel
{
    /**
     * {@inheritDoc}
     */
    public function shouldShareService(string $service): bool
    {
        return parent::shouldShareDefaultService($service);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $service, mixed ...$arguments): object|string|null
    {
        return parent::getDefaultService($service, ...$arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $service): bool
    {
        return parent::isDefaultService($service);
    }
}