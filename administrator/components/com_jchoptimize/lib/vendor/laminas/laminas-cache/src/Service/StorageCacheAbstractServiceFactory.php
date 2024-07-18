<?php

namespace _JchOptimizeVendor\Laminas\Cache\Service;

use _JchOptimizeVendor\Interop\Container\ContainerInterface;
use _JchOptimizeVendor\Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use _JchOptimizeVendor\Webmozart\Assert\Assert;

/**
 * Storage cache factory for multiple caches.
 */
class StorageCacheAbstractServiceFactory implements AbstractFactoryInterface
{
    public const CACHES_CONFIGURATION_KEY = 'caches';

    /** @var null|array<string,mixed> */
    protected $config;

    /**
     * Configuration key for cache objects.
     *
     * @var string
     */
    protected $configKey = self::CACHES_CONFIGURATION_KEY;

    /**
     * Create an object.
     *
     * @param string $requestedName
     *
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $this->getConfig($container);
        $factory = $container->get(StorageAdapterFactoryInterface::class);
        \assert($factory instanceof StorageAdapterFactoryInterface);
        $configForRequestedName = $config[$requestedName] ?? [];
        Assert::isMap($configForRequestedName);
        $factory->assertValidConfigurationStructure($configForRequestedName);

        return $factory->createFromArrayConfiguration($configForRequestedName);
    }

    /**
     * @param string $requestedName
     *
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $this->getConfig($container);
        if (empty($config)) {
            return \false;
        }

        return isset($config[$requestedName]) && \is_array($config[$requestedName]);
    }

    /**
     * Retrieve cache configuration, if any.
     *
     * @return array
     */
    protected function getConfig(ContainerInterface $container)
    {
        if (null !== $this->config) {
            return $this->config;
        }
        if (!$container->has('config')) {
            $this->config = [];

            return $this->config;
        }
        $config = $container->get('config');
        Assert::isArrayAccessible($config);
        if (!isset($config[$this->configKey])) {
            $this->config = [];

            return $this->config;
        }
        $cacheConfigurations = $config[$this->configKey];
        Assert::isMap($cacheConfigurations);

        return $this->config = $cacheConfigurations;
    }
}
