<?php

namespace _JchOptimizeVendor\Laminas\Cache\Service;

use _JchOptimizeVendor\Laminas\Cache\Storage\AdapterPluginManager;
use _JchOptimizeVendor\Psr\Container\ContainerInterface;

final class StorageAdapterPluginManagerFactory
{
    public function __invoke(ContainerInterface $container): AdapterPluginManager
    {
        return new AdapterPluginManager($container);
    }
}
