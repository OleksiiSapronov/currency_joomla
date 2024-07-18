<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Apcu;

use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Apcu;
use _JchOptimizeVendor\Laminas\Cache\Storage\AdapterPluginManager;
use _JchOptimizeVendor\Laminas\ServiceManager\Factory\InvokableFactory;
use _JchOptimizeVendor\Psr\Container\ContainerInterface;

final class AdapterPluginManagerDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): AdapterPluginManager
    {
        $pluginManager = $callback();
        \assert($pluginManager instanceof AdapterPluginManager);
        $pluginManager->configure(['factories' => [Apcu::class => InvokableFactory::class], 'aliases' => ['apcu' => Apcu::class, 'Apcu' => Apcu::class, 'ApcU' => Apcu::class, 'APCu' => Apcu::class]]);

        return $pluginManager;
    }
}
