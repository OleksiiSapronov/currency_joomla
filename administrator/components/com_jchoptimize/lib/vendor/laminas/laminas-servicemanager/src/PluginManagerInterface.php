<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\ServiceManager;

use _JchOptimizeVendor\Laminas\ServiceManager\Exception\InvalidServiceException;
use _JchOptimizeVendor\Psr\Container\ContainerExceptionInterface;

/**
 * Interface for a plugin manager.
 *
 * A plugin manager is a specialized service locator used to create homogeneous objects
 *
 * @template InstanceType
 */
interface PluginManagerInterface extends ServiceLocatorInterface
{
    /**
     * Validate an instance.
     *
     * @param mixed $instance
     *
     * @throws InvalidServiceException     if created instance does not respect the
     *                                     constraint on type imposed by the plugin manager
     * @throws ContainerExceptionInterface if any other error occurs
     *
     * @psalm-assert InstanceType $instance
     */
    public function validate($instance);
}
