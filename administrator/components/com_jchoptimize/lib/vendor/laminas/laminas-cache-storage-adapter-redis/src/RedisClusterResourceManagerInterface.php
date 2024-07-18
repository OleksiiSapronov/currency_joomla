<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter;

use _JchOptimizeVendor\Laminas\Cache\Storage\PluginCapableInterface;
use RedisCluster as RedisClusterFromExtension;

interface RedisClusterResourceManagerInterface
{
    public function getVersion(): string;

    public function getResource(): RedisClusterFromExtension;

    /**
     * @psalm-param RedisClusterOptions::OPT_* $option
     *
     * @return mixed
     */
    public function getLibOption(int $option);

    public function hasSerializationSupport(PluginCapableInterface $adapter): bool;
}
