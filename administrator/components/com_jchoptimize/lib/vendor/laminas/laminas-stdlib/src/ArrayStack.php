<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

use ArrayIterator;
use ArrayObject as PhpArrayObject;
use ReturnTypeWillChange;

/**
 * ArrayObject that acts as a stack with regards to iteration.
 */
class ArrayStack extends PhpArrayObject
{
    /**
     * Retrieve iterator.
     *
     * Retrieve an array copy of the object, reverse its order, and return an
     * ArrayIterator with that reversed array.
     *
     * @return \ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $array = $this->getArrayCopy();

        return new \ArrayIterator(\array_reverse($array));
    }
}
