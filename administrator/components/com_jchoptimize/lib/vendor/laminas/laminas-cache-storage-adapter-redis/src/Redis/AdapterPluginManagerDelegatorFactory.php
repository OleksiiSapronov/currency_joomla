<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Redis;

use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Redis;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\RedisCluster;
use _JchOptimizeVendor\Laminas\Cache\Storage\AdapterPluginManager;
use _JchOptimizeVendor\Laminas\ServiceManager\Factory\InvokableFactory;
use _JchOptimizeVendor\Psr\Container\ContainerInterface;

/**
 * @internal
 */
final class AdapterPluginManagerDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): AdapterPluginManager
    {
        $pluginManager = $callback();
        \assert($pluginManager instanceof AdapterPluginManager);
        $pluginManager->configure(['factories' => [Redis::class => InvokableFactory::class, RedisCluster::class => InvokableFactory::class], 'aliases' => ['redis' => Redis::class, 'Redis' => Redis::class]]);

        return $pluginManager;
    }
}
