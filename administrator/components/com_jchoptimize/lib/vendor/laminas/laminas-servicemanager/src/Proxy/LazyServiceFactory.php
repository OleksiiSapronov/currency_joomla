<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\ServiceManager\Proxy;

use _JchOptimizeVendor\Laminas\ServiceManager\Exception;
use _JchOptimizeVendor\Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use _JchOptimizeVendor\ProxyManager\Factory\LazyLoadingValueHolderFactory;
use _JchOptimizeVendor\ProxyManager\Proxy\LazyLoadingInterface;
use _JchOptimizeVendor\ProxyManager\Proxy\VirtualProxyInterface;
use _JchOptimizeVendor\Psr\Container\ContainerInterface;

/**
 * Delegator factory responsible of instantiating lazy loading value holder proxies of
 * given services at runtime.
 *
 * @see https://github.com/Ocramius/ProxyManager/blob/master/docs/lazy-loading-value-holder.md
 */
final class LazyServiceFactory implements DelegatorFactoryInterface
{
    private LazyLoadingValueHolderFactory $proxyFactory;

    /** @var array<string, class-string> map of service names to class names */
    private array $servicesMap;

    /**
     * @param array<string, class-string> $servicesMap A map of service names to
     *                                                 class names of their respective classes
     */
    public function __construct(LazyLoadingValueHolderFactory $proxyFactory, array $servicesMap)
    {
        $this->proxyFactory = $proxyFactory;
        $this->servicesMap = $servicesMap;
    }

    /**
     * @param string $name
     *
     * @return VirtualProxyInterface
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null)
    {
        if (isset($this->servicesMap[$name])) {
            $initializer = function (&$wrappedInstance, LazyLoadingInterface $proxy) use ($callback) {
                $proxy->setProxyInitializer(null);
                $wrappedInstance = $callback();

                return \true;
            };

            return $this->proxyFactory->createProxy($this->servicesMap[$name], $initializer);
        }

        throw new Exception\ServiceNotFoundException(\sprintf('The requested service "%s" was not found in the provided services map', $name));
    }
}
