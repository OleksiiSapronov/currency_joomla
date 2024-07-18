<?php

/**
 * @see https://github.com/laminas/laminas-serializer for the canonical source repository
 */
declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Serializer;

use _JchOptimizeVendor\Laminas\Serializer\Adapter\AdapterInterface as Adapter;
use _JchOptimizeVendor\Laminas\ServiceManager\ServiceManager;

// phpcs:ignore
abstract class Serializer
{
    /**
     * Plugin manager for loading adapters.
     *
     * @var null|AdapterPluginManager
     */
    protected static $adapters;

    /**
     * The default adapter.
     *
     * @var Adapter|string
     */
    protected static $defaultAdapter = 'PhpSerialize';

    /**
     * Create a serializer adapter instance.
     *
     * @param Adapter|string          $adapterName    Name of the adapter class
     * @param null|array|\Traversable $adapterOptions Serializer options
     *
     * @return Adapter
     */
    public static function factory($adapterName, $adapterOptions = null)
    {
        if ($adapterName instanceof Adapter) {
            return $adapterName;
            // $adapterName is already an adapter object
        }

        return static::getAdapterPluginManager()->get($adapterName, $adapterOptions);
    }

    /**
     * Change the adapter plugin manager.
     */
    public static function setAdapterPluginManager(AdapterPluginManager $adapters)
    {
        static::$adapters = $adapters;
    }

    /**
     * Get the adapter plugin manager.
     *
     * @return AdapterPluginManager
     */
    public static function getAdapterPluginManager()
    {
        if (null === static::$adapters) {
            static::$adapters = new AdapterPluginManager(new ServiceManager());
        }

        return static::$adapters;
    }

    /**
     * Resets the internal adapter plugin manager.
     *
     * @return AdapterPluginManager
     */
    public static function resetAdapterPluginManager()
    {
        static::$adapters = new AdapterPluginManager(new ServiceManager());

        return static::$adapters;
    }

    /**
     * Change the default adapter.
     *
     * @param Adapter|string          $adapter
     * @param null|array|\Traversable $adapterOptions
     */
    public static function setDefaultAdapter($adapter, $adapterOptions = null)
    {
        static::$defaultAdapter = static::factory($adapter, $adapterOptions);
    }

    /**
     * Get the default adapter.
     *
     * @return Adapter
     */
    public static function getDefaultAdapter()
    {
        if (!static::$defaultAdapter instanceof Adapter) {
            static::setDefaultAdapter(static::$defaultAdapter);
        }

        return static::$defaultAdapter;
    }

    /**
     * Generates a storable representation of a value using the default adapter.
     * Optionally different adapter could be provided as second argument.
     *
     * @param mixed                   $value
     * @param Adapter|string          $adapter
     * @param null|array|\Traversable $adapterOptions Adapter constructor options
     *                                                only used to create adapter instance
     *
     * @return string
     */
    public static function serialize($value, $adapter = null, $adapterOptions = null)
    {
        if (null !== $adapter) {
            $adapter = static::factory($adapter, $adapterOptions);
        } else {
            $adapter = static::getDefaultAdapter();
        }

        return $adapter->serialize($value);
    }

    /**
     * Creates a PHP value from a stored representation using the default adapter.
     * Optionally different adapter could be provided as second argument.
     *
     * @param string                  $serialized
     * @param Adapter|string          $adapter
     * @param null|array|\Traversable $adapterOptions Adapter constructor options
     *                                                only used to create adapter instance
     *
     * @return mixed
     */
    public static function unserialize($serialized, $adapter = null, $adapterOptions = null)
    {
        if (null !== $adapter) {
            $adapter = static::factory($adapter, $adapterOptions);
        } else {
            $adapter = static::getDefaultAdapter();
        }

        return $adapter->unserialize($serialized);
    }
}
