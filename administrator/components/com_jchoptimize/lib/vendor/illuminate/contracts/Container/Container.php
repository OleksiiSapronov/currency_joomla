<?php

namespace _JchOptimizeVendor\Illuminate\Contracts\Container;

use _JchOptimizeVendor\Psr\Container\ContainerInterface;
use Closure;

interface Container extends ContainerInterface
{
    /**
     * Determine if the given abstract type has been bound.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function bound($abstract);

    /**
     * Alias a type to a different name.
     *
     * @param string $abstract
     * @param string $alias
     *
     * @throws \LogicException
     */
    public function alias($abstract, $alias);

    /**
     * Assign a set of tags to a given binding.
     *
     * @param array|string $abstracts
     * @param array|mixed  ...$tags
     */
    public function tag($abstracts, $tags);

    /**
     * Resolve all of the bindings for a given tag.
     *
     * @param string $tag
     *
     * @return iterable
     */
    public function tagged($tag);

    /**
     * Register a binding with the container.
     *
     * @param string               $abstract
     * @param null|\Closure|string $concrete
     * @param bool                 $shared
     */
    public function bind($abstract, $concrete = null, $shared = \false);

    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param string               $abstract
     * @param null|\Closure|string $concrete
     * @param bool                 $shared
     */
    public function bindIf($abstract, $concrete = null, $shared = \false);

    /**
     * Register a shared binding in the container.
     *
     * @param string               $abstract
     * @param null|\Closure|string $concrete
     */
    public function singleton($abstract, $concrete = null);

    /**
     * Register a shared binding if it hasn't already been registered.
     *
     * @param string               $abstract
     * @param null|\Closure|string $concrete
     */
    public function singletonIf($abstract, $concrete = null);

    /**
     * "Extend" an abstract type in the container.
     *
     * @param string $abstract
     *
     * @throws \InvalidArgumentException
     */
    public function extend($abstract, \Closure $closure);

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed  $instance
     *
     * @return mixed
     */
    public function instance($abstract, $instance);

    /**
     * Add a contextual binding to the container.
     *
     * @param string          $concrete
     * @param string          $abstract
     * @param \Closure|string $implementation
     */
    public function addContextualBinding($concrete, $abstract, $implementation);

    /**
     * Define a contextual binding.
     *
     * @param array|string $concrete
     *
     * @return \Illuminate\Contracts\Container\ContextualBindingBuilder
     */
    public function when($concrete);

    /**
     * Get a closure to resolve the given type from the container.
     *
     * @param string $abstract
     *
     * @return \Closure
     */
    public function factory($abstract);

    /**
     * Flush the container of all bindings and resolved instances.
     */
    public function flush();

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     *
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function make($abstract, array $parameters = []);

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param callable|string $callback
     * @param null|string     $defaultMethod
     *
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = null);

    /**
     * Determine if the given abstract type has been resolved.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function resolved($abstract);

    /**
     * Register a new resolving callback.
     *
     * @param \Closure|string $abstract
     */
    public function resolving($abstract, \Closure $callback = null);

    /**
     * Register a new after resolving callback.
     *
     * @param \Closure|string $abstract
     */
    public function afterResolving($abstract, \Closure $callback = null);
}
