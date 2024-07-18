<?php

namespace _JchOptimizeVendor\Laminas\Paginator\Adapter\Service;

use _JchOptimizeVendor\Interop\Container\ContainerInterface;
use _JchOptimizeVendor\Laminas\Paginator\Iterator as IteratorAdapter;
use _JchOptimizeVendor\Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use _JchOptimizeVendor\Laminas\ServiceManager\FactoryInterface;
use _JchOptimizeVendor\Laminas\ServiceManager\ServiceLocatorInterface;

class IteratorFactory implements FactoryInterface
{
    /**
     * Options to use when creating adapter (v2).
     *
     * @var null|array
     */
    protected $creationOptions;

    /**
     * @return IteratorAdapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        if (null === $options || empty($options)) {
            throw new ServiceNotCreatedException(\sprintf('%s requires a minimum of an Iterator instance', IteratorAdapter::class));
        }
        $iterator = \array_shift($options);
        if (!$iterator instanceof \Iterator) {
            throw new ServiceNotCreatedException(\sprintf('%s requires an Iterator instance; received %s', IteratorAdapter::class, \is_object($iterator) ? \get_class($iterator) : \gettype($iterator)));
        }

        return new $requestedName($iterator);
    }

    /**
     * Create and return an IteratorAdapter instance (v2).
     *
     * @param null|string $name
     * @param string      $requestedName
     *
     * @return IteratorAdapter
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = IteratorAdapter::class)
    {
        return $this($container, $requestedName, $this->creationOptions);
    }

    /**
     * Options to use with factory (v2).
     */
    public function setCreationOptions(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }
}
