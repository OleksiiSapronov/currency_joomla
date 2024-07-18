<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Filesystem;

use _JchOptimizeVendor\Laminas\Cache\Storage\AdapterPluginManager;

final class ConfigProvider
{
    /**
     * @return array<string,mixed>
     */
    public function __invoke(): array
    {
        return ['dependencies' => $this->getServiceDependencies()];
    }

    /**
     * @return array<string,mixed>
     */
    public function getServiceDependencies(): array
    {
        return ['delegators' => [AdapterPluginManager::class => [AdapterPluginManagerDelegatorFactory::class]]];
    }
}
