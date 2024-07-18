<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\ServiceManager;

use _JchOptimizeVendor\Psr\Container\ContainerExceptionInterface;
use _JchOptimizeVendor\Psr\Container\ContainerInterface;

/**
 * Interface for service locator.
 */
interface ServiceLocatorInterface extends ContainerInterface
{
    /**
     * Build a service by its name, using optional options (such services are NEVER cached).
     *
     * @template T of object
     *
     * @param class-string<T>|string $name
     * @param null|array<mixed>      $options
     *
     * @return mixed
     *
     * @psalm-return ($name is class-string<T> ? T : mixed)
     *
     * @throws Exception\ServiceNotFoundException   if no factory/abstract
     *                                              factory could be found to create the instance
     * @throws Exception\ServiceNotCreatedException if factory/delegator fails
     *                                              to create the instance
     * @throws ContainerExceptionInterface          if any other error occurs
     */
    public function build($name, ?array $options = null);
}
