<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Service;

use _JchOptimizeVendor\Laminas\Cache\Storage\AdapterPluginManager;
use _JchOptimizeVendor\Psr\Container\ContainerInterface;

final class StorageAdapterFactoryFactory
{
    public function __invoke(ContainerInterface $container): StorageAdapterFactory
    {
        return new StorageAdapterFactory($container->get(AdapterPluginManager::class), $container->get(StoragePluginFactoryInterface::class));
    }
}
