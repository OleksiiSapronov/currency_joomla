<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter;

use _JchOptimizeVendor\Laminas\Cache\Storage\IteratorInterface;
use APCUIterator as BaseApcuIterator;
use ReturnTypeWillChange;

final class ApcuIterator implements IteratorInterface
{
    /**
     * The storage instance.
     *
     * @var Apcu
     */
    private $storage;

    /**
     * The iterator mode.
     *
     * @var int
     *
     * @psalm-var IteratorInterface::CURRENT_AS_*
     */
    private $mode = IteratorInterface::CURRENT_AS_KEY;

    /**
     * The base APCIterator instance.
     *
     * @var BaseApcuIterator
     */
    private $baseIterator;

    /**
     * The length of the namespace prefix.
     *
     * @var int
     */
    private $prefixLength;

    public function __construct(Apcu $storage, BaseApcuIterator $baseIterator, string $prefix)
    {
        $this->storage = $storage;
        $this->baseIterator = $baseIterator;
        $this->prefixLength = \strlen($prefix);
    }

    public function getStorage(): Apcu
    {
        return $this->storage;
    }

    /**
     * Get iterator mode.
     *
     * @return int Value of IteratorInterface::CURRENT_AS_*
     *
     * @psalm-return IteratorInterface::CURRENT_AS_*
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * Set iterator mode.
     *
     * @param int $mode
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @psalm-param IteratorInterface::CURRENT_AS_* $mode
     *
     * @return ApcuIterator Provides a fluent interface
     */
    public function setMode($mode)
    {
        $this->mode = (int) $mode;

        return $this;
    }

    // Iterator
    /**
     * Get current key, value or metadata.
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (IteratorInterface::CURRENT_AS_SELF === $this->mode) {
            return $this;
        }
        $key = $this->key();
        if (IteratorInterface::CURRENT_AS_VALUE === $this->mode) {
            return $this->storage->getItem($key);
        }
        if (IteratorInterface::CURRENT_AS_METADATA === $this->mode) {
            return $this->storage->getMetadata($key);
        }

        return $key;
    }

    public function key(): string
    {
        $key = $this->baseIterator->key();
        // remove namespace prefix
        return \substr($key, $this->prefixLength);
    }

    /**
     * Move forward to next element.
     */
    public function next(): void
    {
        $this->baseIterator->next();
    }

    /**
     * Checks if current position is valid.
     */
    public function valid(): bool
    {
        return $this->baseIterator->valid();
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind(): void
    {
        $this->baseIterator->rewind();
    }
}
