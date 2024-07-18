<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

use ArrayObject as PhpArrayObject;
use ReturnTypeWillChange;

class Parameters extends PhpArrayObject implements ParametersInterface
{
    /**
     * Constructor.
     *
     * Enforces that we have an array, and enforces parameter access to array
     * elements.
     *
     * @param array $values
     */
    public function __construct(?array $values = null)
    {
        if (null === $values) {
            $values = [];
        }
        parent::__construct($values, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Populate from native PHP array.
     */
    public function fromArray(array $values)
    {
        $this->exchangeArray($values);
    }

    /**
     * Populate from query string.
     *
     * @param string $string
     */
    public function fromString($string)
    {
        $array = [];
        \parse_str($string, $array);
        $this->fromArray($array);
    }

    /**
     * Serialize to native PHP array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * Serialize to query string.
     *
     * @return string
     */
    public function toString()
    {
        return \http_build_query($this->toArray());
    }

    /**
     * Retrieve by key.
     *
     * Returns null if the key does not exist.
     *
     * @param string $name
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($name)
    {
        if ($this->offsetExists($name)) {
            return parent::offsetGet($name);
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed  $default optional default value
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if ($this->offsetExists($name)) {
            return parent::offsetGet($name);
        }

        return $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return Parameters
     */
    public function set($name, $value)
    {
        $this[$name] = $value;

        return $this;
    }
}
