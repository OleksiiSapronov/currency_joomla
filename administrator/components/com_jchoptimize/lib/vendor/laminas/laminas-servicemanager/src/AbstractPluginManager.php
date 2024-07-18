<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\ServiceManager;

use _JchOptimizeVendor\Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use _JchOptimizeVendor\Laminas\ServiceManager\Exception\InvalidServiceException;
use _JchOptimizeVendor\Psr\Container\ContainerInterface;

/**
 * Abstract plugin manager.
 *
 * Abstract PluginManagerInterface implementation providing:
 *
 * - creation context support. The constructor accepts the parent container
 *   instance, which is then used when creating instances.
 * - plugin validation. Implementations may define the `$instanceOf` property
 *   to indicate what class types constitute valid plugins, omitting the
 *   requirement to define the `validate()` method.
 *
 * The implementation extends `ServiceManager`, thus providing the same set
 * of capabilities as found in that implementation.
 *
 * @template InstanceType
 *
 * @implements PluginManagerInterface<InstanceType>
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractPluginManager extends ServiceManager implements PluginManagerInterface
{
    /**
     * Whether or not to auto-add a FQCN as an invokable if it exists.
     *
     * @var bool
     */
    protected $autoAddInvokableClass = \true;

    /**
     * An object type that the created instance must be instanced of.
     *
     * @var null|string
     *
     * @psalm-var null|class-string<InstanceType>
     */
    protected $instanceOf;

    /**
     * Sets the provided $parentLocator as the creation context for all
     * factories; for $config, {@see \Laminas\ServiceManager\ServiceManager::configure()}
     * for details on its accepted structure.
     *
     * @param null|ConfigInterface|ContainerInterface $configInstanceOrParentLocator
     *
     * @psalm-param ServiceManagerConfiguration $config
     */
    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        // @psalm-suppress DocblockTypeContradiction
        if (null !== $configInstanceOrParentLocator && !$configInstanceOrParentLocator instanceof ConfigInterface && !$configInstanceOrParentLocator instanceof ContainerInterface) {
            throw new Exception\InvalidArgumentException(\sprintf('%s expects a ConfigInterface or ContainerInterface instance as the first argument; received %s', self::class, \is_object($configInstanceOrParentLocator) ? \get_class($configInstanceOrParentLocator) : \gettype($configInstanceOrParentLocator)));
        }
        if ($configInstanceOrParentLocator instanceof ConfigInterface) {
            \trigger_error(\sprintf('Usage of %s as a constructor argument for %s is now deprecated', ConfigInterface::class, static::class), \E_USER_DEPRECATED);
            $config = $configInstanceOrParentLocator->toArray();
        }
        parent::__construct($config);
        if (!$configInstanceOrParentLocator instanceof ContainerInterface) {
            \trigger_error(\sprintf('%s now expects a %s instance representing the parent container; please update your code', __METHOD__, ContainerInterface::class), \E_USER_DEPRECATED);
        }
        $this->creationContext = $configInstanceOrParentLocator instanceof ContainerInterface ? $configInstanceOrParentLocator : $this;
    }

    /**
     * Override configure() to validate service instances.
     *
     * @psalm-param ServiceManagerConfiguration $config
     *
     * @return self
     *
     * @throws InvalidServiceException                   if an instance passed in the `services` configuration is invalid for the
     *                                                   plugin manager
     * @throws ContainerModificationsNotAllowedException if the allow override flag has been toggled off, and a
     *                                                   service instanceexists for a given service
     */
    public function configure(array $config)
    {
        if (isset($config['services'])) {
            // @psalm-suppress MixedAssignment
            foreach ($config['services'] as $service) {
                $this->validate($service);
            }
        }
        parent::configure($config);

        return $this;
    }

    /**
     * Override setService for additional plugin validation.
     *
     * {@inheritDoc}
     *
     * @param class-string<InstanceType>|string $name
     * @param InstanceType                      $service
     */
    public function setService($name, $service)
    {
        $this->validate($service);
        parent::setService($name, $service);
    }

    /**
     * @param class-string<InstanceType>|string $name    service name of plugin to retrieve
     * @param null|array<mixed>                 $options options to use when creating the instance
     *
     * @return mixed
     *
     * @psalm-return ($name is class-string<InstanceType> ? InstanceType : mixed)
     *
     * @throws Exception\ServiceNotFoundException if the manager does not have
     *                                            a service definition for the instance, and the service is not
     *                                            auto-invokable
     * @throws InvalidServiceException            if the plugin created is invalid for the
     *                                            plugin context
     */
    public function get($name, ?array $options = null)
    {
        if (!$this->has($name)) {
            if (!$this->autoAddInvokableClass || !\class_exists($name)) {
                throw new Exception\ServiceNotFoundException(\sprintf('A plugin by the name "%s" was not found in the plugin manager %s', $name, static::class));
            }
            $this->setFactory($name, Factory\InvokableFactory::class);
        }
        $instance = !$options ? parent::get($name) : $this->build($name, $options);
        $this->validate($instance);

        return $instance;
    }

    /**
     * @psalm-assert InstanceType $instance
     */
    public function validate($instance)
    {
        if (\method_exists($this, 'validatePlugin')) {
            \trigger_error(\sprintf('%s::validatePlugin() has been deprecated as of 3.0; please define validate() instead', static::class), \E_USER_DEPRECATED);
            $this->validatePlugin($instance);

            return;
        }
        if (empty($this->instanceOf) || $instance instanceof $this->instanceOf) {
            return;
        }

        throw new InvalidServiceException(\sprintf('Plugin manager "%s" expected an instance of type "%s", but "%s" was received', self::class, $this->instanceOf, \is_object($instance) ? \get_class($instance) : \gettype($instance)));
    }

    /**
     * Implemented for backwards compatibility only.
     *
     * Returns the creation context.
     *
     * @deprecated since 3.0.0. The creation context should be passed during
     *     instantiation instead.
     */
    public function setServiceLocator(ContainerInterface $container)
    {
        \trigger_error(\sprintf('Usage of %s is deprecated since v3.0.0; please pass the container to the constructor instead', __METHOD__), \E_USER_DEPRECATED);
        $this->creationContext = $container;
    }
}
