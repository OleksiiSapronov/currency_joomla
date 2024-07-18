<?php

namespace _JchOptimizeVendor\Illuminate\Support;

use Closure;

abstract class MultipleInstanceManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved instances.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The registered custom instance creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new manager instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Dynamically call the default instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->instance()->{$method}(...$parameters);
    }

    /**
     * Get the default instance name.
     *
     * @return string
     */
    abstract public function getDefaultInstance();

    /**
     * Set the default instance name.
     *
     * @param string $name
     */
    abstract public function setDefaultInstance($name);

    /**
     * Get the instance specific configuration.
     *
     * @param string $name
     *
     * @return array
     */
    abstract public function getInstanceConfig($name);

    /**
     * Get an instance instance by name.
     *
     * @param null|string $name
     *
     * @return mixed
     */
    public function instance($name = null)
    {
        $name = $name ?: $this->getDefaultInstance();

        return $this->instances[$name] = $this->get($name);
    }

    /**
     * Unset the given instances.
     *
     * @param null|array|string $name
     *
     * @return $this
     */
    public function forgetInstance($name = null)
    {
        $name = $name ?? $this->getDefaultInstance();
        foreach ((array) $name as $instanceName) {
            if (isset($this->instances[$instanceName])) {
                unset($this->instances[$instanceName]);
            }
        }

        return $this;
    }

    /**
     * Disconnect the given instance and remove from local cache.
     *
     * @param null|string $name
     */
    public function purge($name = null)
    {
        $name = $name ?? $this->getDefaultInstance();
        unset($this->instances[$name]);
    }

    /**
     * Register a custom instance creator Closure.
     *
     * @param string $name
     *
     * @return $this
     */
    public function extend($name, \Closure $callback)
    {
        $this->customCreators[$name] = $callback->bindTo($this, $this);

        return $this;
    }

    /**
     * Attempt to get an instance from the local cache.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function get($name)
    {
        return $this->instances[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given instance.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getInstanceConfig($name);
        if (\is_null($config)) {
            throw new \InvalidArgumentException("Instance [{$name}] is not defined.");
        }
        if (!\array_key_exists('driver', $config)) {
            throw new \RuntimeException("Instance [{$name}] does not specify a driver.");
        }
        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }
        $driverMethod = 'create'.\ucfirst($config['driver']).'Driver';
        if (\method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }

        throw new \InvalidArgumentException("Instance driver [{$config['driver']}] is not supported.");
    }

    /**
     * Call a custom instance creator.
     *
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }
}
