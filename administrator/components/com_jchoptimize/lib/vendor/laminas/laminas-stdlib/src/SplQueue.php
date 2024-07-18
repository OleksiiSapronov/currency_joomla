<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

use ReturnTypeWillChange;
use Serializable;

use function serialize;
use function unserialize;

/**
 * Serializable version of SplQueue.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @extends \SplQueue<TValue>
 */
class SplQueue extends \SplQueue implements \Serializable
{
    /**
     * Magic method used for serializing of an instance.
     *
     * @return list<TValue>
     */
    #[\ReturnTypeWillChange]
    public function __serialize()
    {
        return $this->toArray();
    }

    /**
     * Magic method used to rebuild an instance.
     *
     * @param array<array-key, TValue> $data data array
     */
    #[\ReturnTypeWillChange]
    public function __unserialize($data)
    {
        foreach ($data as $item) {
            $this->push($item);
        }
    }

    /**
     * Return an array representing the queue.
     *
     * @return list<TValue>
     */
    public function toArray()
    {
        $array = [];
        foreach ($this as $item) {
            $array[] = $item;
        }

        return $array;
    }

    /**
     * Serialize.
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function serialize()
    {
        return \serialize($this->__serialize());
    }

    /**
     * Unserialize.
     *
     * @param string $data
     */
    #[\ReturnTypeWillChange]
    public function unserialize($data)
    {
        $toUnserialize = \unserialize($data);
        if (!\is_array($toUnserialize)) {
            throw new \UnexpectedValueException(\sprintf('Cannot deserialize %s instance; corrupt serialization data', self::class));
        }
        $this->__unserialize($toUnserialize);
    }
}
