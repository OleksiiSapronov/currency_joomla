<?php

namespace _JchOptimizeVendor\Laminas\Paginator\Adapter\Service;

use _JchOptimizeVendor\Interop\Container\ContainerInterface;
use _JchOptimizeVendor\Laminas\Paginator\Adapter\DbSelect;
use _JchOptimizeVendor\Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use _JchOptimizeVendor\Laminas\ServiceManager\FactoryInterface;
use _JchOptimizeVendor\Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @deprecated 2.10.0 Use the adapters in laminas/laminas-paginator-adapter-laminasdb.
 */
class DbSelectFactory implements FactoryInterface
{
    /**
     * Options to use when creating adapter (v2).
     *
     * @var null|array
     */
    protected $creationOptions;

    /**
     * @return DbSelect
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        if (null === $options || empty($options)) {
            throw new ServiceNotCreatedException(\sprintf('%s requires a minimum of laminas-db Sql\\Select and Adapter instance', DbSelect::class));
        }

        return new $requestedName($options[0], $options[1], $options[2] ?? null, $options[3] ?? null);
    }

    /**
     * Create and return a DbSelect instance (v2).
     *
     * @param null|string $name
     * @param string      $requestedName
     *
     * @return DbSelect
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = DbSelect::class)
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
