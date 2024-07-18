<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

use ArrayAccess;
use Iterator;
use ReturnTypeWillChange;

use function serialize;
use function unserialize;

/**
 * Custom framework ArrayObject implementation.
 *
 * Extends version-specific "abstract" implementation.
 */
class ArrayObject implements \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{
    /**
     * Properties of the object have their normal functionality
     * when accessed as list (var_dump, foreach, etc.).
     */
    public const STD_PROP_LIST = 1;

    /**
     * Entries can be accessed as properties (read and write).
     */
    public const ARRAY_AS_PROPS = 2;

    /** @var array */
    protected $storage;

    /** @var int */
    protected $flag;

    /** @var string */
    protected $iteratorClass;

    /** @var array */
    protected $protectedProperties;

    /**
     * Constructor.
     *
     * @param array|object $input         Object values must act like ArrayAccess
     * @param int          $flags
     * @param string       $iteratorClass
     */
    public function __construct($input = [], $flags = self::STD_PROP_LIST, $iteratorClass = 'ArrayIterator')
    {
        $this->setFlags($flags);
        $this->storage = $input;
        $this->setIteratorClass($iteratorClass);
        $this->protectedProperties = \array_keys(\get_object_vars($this));
    }

    /**
     * Returns whether the requested key exists.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        if (self::ARRAY_AS_PROPS === $this->flag) {
            return $this->offsetExists($key);
        }
        if (\in_array($key, $this->protectedProperties)) {
            throw new Exception\InvalidArgumentException("{$key} is a protected property, use a different key");
        }

        return isset($this->{$key});
    }

    /**
     * Sets the value at the specified key to value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (self::ARRAY_AS_PROPS === $this->flag) {
            $this->offsetSet($key, $value);

            return;
        }
        if (\in_array($key, $this->protectedProperties)) {
            throw new Exception\InvalidArgumentException("{$key} is a protected property, use a different key");
        }
        $this->{$key} = $value;
    }

    /**
     * Unsets the value at the specified key.
     *
     * @param mixed $key
     */
    public function __unset($key)
    {
        if (self::ARRAY_AS_PROPS === $this->flag) {
            $this->offsetUnset($key);

            return;
        }
        if (\in_array($key, $this->protectedProperties)) {
            throw new Exception\InvalidArgumentException("{$key} is a protected property, use a different key");
        }
        unset($this->{$key});
    }

    /**
     * Magic method used for serializing of an instance.
     *
     * @return array
     */
    public function __serialize()
    {
        return \get_object_vars($this);
    }

    /**
     * Magic method used to rebuild an instance.
     *
     * @param array $data data array
     */
    public function __unserialize($data)
    {
        $this->protectedProperties = \array_keys(\get_object_vars($this));
        // Unserialize protected internal properties first
        if (\array_key_exists('flag', $data)) {
            $this->setFlags((int) $data['flag']);
            unset($data['flag']);
        }
        if (\array_key_exists('storage', $data)) {
            if (!\is_array($data['storage']) && !\is_object($data['storage'])) {
                throw new \UnexpectedValueException(\sprintf('Cannot deserialize %s instance: corrupt storage data; expected array or object, received %s', self::class, \gettype($data['storage'])));
            }
            $this->exchangeArray($data['storage']);
            unset($data['storage']);
        }
        if (\array_key_exists('iteratorClass', $data)) {
            if (!\is_string($data['iteratorClass'])) {
                throw new \UnexpectedValueException(\sprintf('Cannot deserialize %s instance: invalid iteratorClass; expected string, received %s', self::class, \is_object($data['iteratorClass']) ? \get_class($data['iteratorClass']) : \gettype($data['iteratorClass'])));
            }
            $this->setIteratorClass($data['iteratorClass']);
            unset($data['iteratorClass']);
        }
        unset($data['protectedProperties']);
        // Unserialize array keys after resolving protected properties to ensure configuration is used.
        foreach ($data as $k => $v) {
            $this->__set($k, $v);
        }
    }

    /**
     * Returns the value at the specified key by reference.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function &__get($key)
    {
        if (self::ARRAY_AS_PROPS === $this->flag) {
            $ret = &$this->offsetGet($key);

            return $ret;
        }
        if (\in_array($key, $this->protectedProperties, \true)) {
            throw new Exception\InvalidArgumentException("{$key} is a protected property, use a different key");
        }

        return $this->{$key};
    }

    /**
     * Appends the value.
     *
     * @param mixed $value
     */
    public function append($value)
    {
        $this->storage[] = $value;
    }

    /**
     * Sort the entries by value.
     */
    public function asort()
    {
        \asort($this->storage);
    }

    /**
     * Get the number of public properties in the ArrayObject.
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return \count($this->storage);
    }

    /**
     * Exchange the array for another one.
     *
     * @param array|ArrayIterator|ArrayObject|object $data
     *
     * @return array
     */
    public function exchangeArray($data)
    {
        if (!\is_array($data) && !\is_object($data)) {
            throw new Exception\InvalidArgumentException('Passed variable is not an array or object, using empty array instead');
        }
        if (\is_object($data) && ($data instanceof self || $data instanceof \ArrayObject)) {
            $data = $data->getArrayCopy();
        }
        if (!\is_array($data)) {
            $data = (array) $data;
        }
        $storage = $this->storage;
        $this->storage = $data;

        return $storage;
    }

    /**
     * Creates a copy of the ArrayObject.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->storage;
    }

    /**
     * Gets the behavior flags.
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->flag;
    }

    /**
     * Create a new iterator from an ArrayObject instance.
     *
     * @return \Iterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $class = $this->iteratorClass;

        return new $class($this->storage);
    }

    /**
     * Gets the iterator classname for the ArrayObject.
     *
     * @return string
     */
    public function getIteratorClass()
    {
        return $this->iteratorClass;
    }

    /**
     * Sort the entries by key.
     */
    public function ksort()
    {
        \ksort($this->storage);
    }

    /**
     * Sort an array using a case insensitive "natural order" algorithm.
     */
    public function natcasesort()
    {
        \natcasesort($this->storage);
    }

    /**
     * Sort entries using a "natural order" algorithm.
     */
    public function natsort()
    {
        \natsort($this->storage);
    }

    /**
     * Returns whether the requested key exists.
     *
     * @param mixed $key
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return isset($this->storage[$key]);
    }

    /**
     * Returns the value at the specified key.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function &offsetGet($key)
    {
        $ret = null;
        if (!$this->offsetExists($key)) {
            return $ret;
        }
        $ret = &$this->storage[$key];

        return $ret;
    }

    /**
     * Sets the value at the specified key to value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * Unsets the value at the specified key.
     *
     * @param mixed $key
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        if ($this->offsetExists($key)) {
            unset($this->storage[$key]);
        }
    }

    /**
     * Serialize an ArrayObject.
     *
     * @return string
     */
    public function serialize()
    {
        return \serialize($this->__serialize());
    }

    /**
     * Sets the behavior flags.
     *
     * @param int $flags
     */
    public function setFlags($flags)
    {
        $this->flag = $flags;
    }

    /**
     * Sets the iterator classname for the ArrayObject.
     *
     * @param string $class
     */
    public function setIteratorClass($class)
    {
        if (\class_exists($class)) {
            $this->iteratorClass = $class;

            return;
        }
        if (0 === \strpos($class, '\\')) {
            $class = '\\'.$class;
            if (\class_exists($class)) {
                $this->iteratorClass = $class;

                return;
            }
        }

        throw new Exception\InvalidArgumentException('The iterator class does not exist');
    }

    /**
     * Sort the entries with a user-defined comparison function and maintain key association.
     *
     * @param callable $function
     */
    public function uasort($function)
    {
        if (\is_callable($function)) {
            \uasort($this->storage, $function);
        }
    }

    /**
     * Sort the entries by keys using a user-defined comparison function.
     *
     * @param callable $function
     */
    public function uksort($function)
    {
        if (\is_callable($function)) {
            \uksort($this->storage, $function);
        }
    }

    /**
     * Unserialize an ArrayObject.
     *
     * @param string $data
     */
    public function unserialize($data)
    {
        $toUnserialize = \unserialize($data);
        if (!\is_array($toUnserialize)) {
            throw new \UnexpectedValueException(\sprintf('Cannot deserialize %s instance; corrupt serialization data', self::class));
        }
        $this->__unserialize($toUnserialize);
    }
}
