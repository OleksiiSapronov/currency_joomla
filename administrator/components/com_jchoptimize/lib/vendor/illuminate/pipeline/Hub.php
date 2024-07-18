<?php

namespace _JchOptimizeVendor\Illuminate\Pipeline;

use _JchOptimizeVendor\Illuminate\Contracts\Container\Container;
use _JchOptimizeVendor\Illuminate\Contracts\Pipeline\Hub as HubContract;

class Hub implements HubContract
{
    /**
     * The container implementation.
     *
     * @var null|\Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * All of the available pipelines.
     *
     * @var array
     */
    protected $pipelines = [];

    /**
     * Create a new Hub instance.
     *
     * @param null|\Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }

    /**
     * Define the default named pipeline.
     */
    public function defaults(\Closure $callback)
    {
        return $this->pipeline('default', $callback);
    }

    /**
     * Define a new named pipeline.
     *
     * @param string $name
     */
    public function pipeline($name, \Closure $callback)
    {
        $this->pipelines[$name] = $callback;
    }

    /**
     * Send an object through one of the available pipelines.
     *
     * @param mixed       $object
     * @param null|string $pipeline
     *
     * @return mixed
     */
    public function pipe($object, $pipeline = null)
    {
        $pipeline = $pipeline ?: 'default';

        return \call_user_func($this->pipelines[$pipeline], new Pipeline($this->container), $object);
    }

    /**
     * Get the container instance used by the hub.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the container instance used by the hub.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}
