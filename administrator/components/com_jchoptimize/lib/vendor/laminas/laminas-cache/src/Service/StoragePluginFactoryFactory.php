<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Service;

use _JchOptimizeVendor\Laminas\Cache\Storage\PluginManager;
use _JchOptimizeVendor\Psr\Container\ContainerInterface;

final class StoragePluginFactoryFactory
{
    public function __invoke(ContainerInterface $container): StoragePluginFactory
    {
        return new StoragePluginFactory($container->get(PluginManager::class));
    }
}
