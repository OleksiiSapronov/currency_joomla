<?php

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter;

use _JchOptimizeVendor\Laminas\Cache\Exception;
use _JchOptimizeVendor\Laminas\Cache\Storage\Event;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\Laminas\EventManager\EventsCapableInterface;
use _JchOptimizeVendor\Laminas\Stdlib\AbstractOptions;
use _JchOptimizeVendor\Laminas\Stdlib\ErrorHandler;

/**
 * Unless otherwise marked, all options in this class affect all adapters.
 */
class AdapterOptions extends AbstractOptions
{
    // @codingStandardsIgnoreStart
    /**
     * Prioritized properties ordered by prio to be set first
     * in case a bulk of options sets set at once.
     *
     * @var string[]
     */
    protected $__prioritizedProperties__ = [];
    // @codingStandardsIgnoreEnd
    /**
     * The adapter using these options.
     *
     * @var null|StorageInterface
     */
    protected $adapter;

    /**
     * Validate key against pattern.
     *
     * @var string
     */
    protected $keyPattern = '';

    /**
     * Namespace option.
     *
     * @var string
     */
    protected $namespace = 'laminascache';

    /**
     * Readable option.
     *
     * @var bool
     */
    protected $readable = \true;

    /**
     * TTL option.
     *
     * @var float|int 0 means infinite or maximum of adapter
     */
    protected $ttl = 0;

    /**
     * Writable option.
     *
     * @var bool
     */
    protected $writable = \true;

    /**
     * Adapter using this instance.
     *
     * @return AdapterOptions Provides a fluent interface
     */
    public function setAdapter(?StorageInterface $adapter = null)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Set key pattern.
     *
     * @param string $keyPattern
     *
     * @return AdapterOptions Provides a fluent interface
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setKeyPattern($keyPattern)
    {
        $keyPattern = (string) $keyPattern;
        if ($this->keyPattern !== $keyPattern) {
            // validate pattern
            if ('' !== $keyPattern) {
                ErrorHandler::start(\E_WARNING);
                $result = \preg_match($keyPattern, '');
                $error = ErrorHandler::stop();
                if (\false === $result) {
                    throw new Exception\InvalidArgumentException(\sprintf('Invalid pattern "%s"%s', $keyPattern, $error ? ': '.$error->getMessage() : ''), 0, $error);
                }
            }
            $this->triggerOptionEvent('key_pattern', $keyPattern);
            $this->keyPattern = $keyPattern;
        }

        return $this;
    }

    /**
     * Get key pattern.
     *
     * @return string
     */
    public function getKeyPattern()
    {
        return $this->keyPattern;
    }

    /**
     * Set namespace.
     *
     * @param string $namespace
     *
     * @return AdapterOptions Provides a fluent interface
     */
    public function setNamespace($namespace)
    {
        $namespace = (string) $namespace;
        if ($this->namespace !== $namespace) {
            $this->triggerOptionEvent('namespace', $namespace);
            $this->namespace = $namespace;
        }

        return $this;
    }

    /**
     * Get namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Enable/Disable reading data from cache.
     *
     * @param bool $readable
     *
     * @return AdapterOptions Provides a fluent interface
     */
    public function setReadable($readable)
    {
        $readable = (bool) $readable;
        if ($this->readable !== $readable) {
            $this->triggerOptionEvent('readable', $readable);
            $this->readable = $readable;
        }

        return $this;
    }

    /**
     * If reading data from cache enabled.
     *
     * @return bool
     */
    public function getReadable()
    {
        return $this->readable;
    }

    /**
     * Set time to live.
     *
     * @param float|int $ttl
     *
     * @return AdapterOptions Provides a fluent interface
     */
    public function setTtl($ttl)
    {
        $this->normalizeTtl($ttl);
        if ($this->ttl !== $ttl) {
            $this->triggerOptionEvent('ttl', $ttl);
            $this->ttl = $ttl;
        }

        return $this;
    }

    /**
     * Get time to live.
     *
     * @return float
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Enable/Disable writing data to cache.
     *
     * @param bool $writable
     *
     * @return AdapterOptions Provides a fluent interface
     */
    public function setWritable($writable)
    {
        $writable = (bool) $writable;
        if ($this->writable !== $writable) {
            $this->triggerOptionEvent('writable', $writable);
            $this->writable = $writable;
        }

        return $this;
    }

    /**
     * If writing data to cache enabled.
     *
     * @return bool
     */
    public function getWritable()
    {
        return $this->writable;
    }

    /**
     * Cast to array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        $transform = function ($letters) {
            $letter = \array_shift($letters);

            return '_'.\strtolower($letter);
        };
        foreach ($this as $key => $value) {
            if ('__strictMode__' === $key || '__prioritizedProperties__' === $key) {
                continue;
            }
            $normalizedKey = \preg_replace_callback('/([A-Z])/', $transform, $key);
            $array[$normalizedKey] = $value;
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     *
     * NOTE: This method was overwritten just to support prioritized properties
     *       {@link https://github.com/zendframework/zf2/issues/6381}
     *
     * @param AbstractOptions|array|\Traversable $options
     *
     * @return AbstractOptions Provides fluent interface
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setFromArray($options)
    {
        if ($this->__prioritizedProperties__) {
            if ($options instanceof AbstractOptions) {
                $options = $options->toArray();
            }
            if ($options instanceof \Traversable) {
                $options = \iterator_to_array($options);
            } elseif (!\is_array($options)) {
                throw new Exception\InvalidArgumentException(\sprintf('Parameter provided to %s must be an %s, %s or %s', __METHOD__, 'array', 'Traversable', AbstractOptions::class));
            }
            // Sort prioritized options to top
            $options = \array_change_key_case($options, \CASE_LOWER);
            foreach (\array_reverse($this->__prioritizedProperties__) as $key) {
                if (isset($options[$key])) {
                    $options = [$key => $options[$key]] + $options;
                } elseif (isset($options[$key = \str_replace('_', '', $key)])) {
                    $options = [$key => $options[$key]] + $options;
                }
            }
        }

        return parent::setFromArray($options);
    }

    /**
     * Triggers an option event if this options instance has a connection to
     * an adapter implements EventsCapableInterface.
     *
     * @param string $optionName
     * @param mixed  $optionValue
     */
    protected function triggerOptionEvent($optionName, $optionValue)
    {
        if ($this->adapter instanceof EventsCapableInterface) {
            $event = new Event('option', $this->adapter, new \ArrayObject([$optionName => $optionValue]));
            $this->adapter->getEventManager()->triggerEvent($event);
        }
    }

    /**
     * Validates and normalize a TTL.
     *
     * @param float|int $ttl
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeTtl(&$ttl)
    {
        if (!\is_int($ttl)) {
            $ttl = (float) $ttl;
            // convert to int if possible
            if ($ttl === (float) (int) $ttl) {
                $ttl = (int) $ttl;
            }
        }
        if ($ttl < 0) {
            throw new Exception\InvalidArgumentException("TTL can't be negative");
        }
    }
}
