<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter;

use _JchOptimizeVendor\Laminas\Cache\Storage\IteratorInterface;
use GlobIterator;
use ReturnTypeWillChange;

final class FilesystemIterator implements IteratorInterface
{
    /**
     * The Filesystem storage instance.
     *
     * @var Filesystem
     */
    private $storage;

    /**
     * The iterator mode.
     *
     * @var int
     */
    private $mode = IteratorInterface::CURRENT_AS_KEY;

    /**
     * The GlobIterator instance.
     *
     * @var \GlobIterator
     */
    private $globIterator;

    /**
     * The namespace sprefix.
     *
     * @var string
     */
    private $prefix;

    /**
     * String length of namespace prefix.
     *
     * @var int
     */
    private $prefixLength;

    /**
     * Constructor.
     *
     * @param string $path
     * @param string $prefix
     */
    public function __construct(Filesystem $storage, $path, $prefix)
    {
        $this->storage = $storage;
        $this->globIterator = new \GlobIterator($path, \GlobIterator::KEY_AS_FILENAME);
        $this->prefix = $prefix;
        $this->prefixLength = \strlen($prefix);
    }

    /**
     * Get storage instance.
     *
     * @return Filesystem
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Get iterator mode.
     *
     * @return int Value of IteratorInterface::CURRENT_AS_*
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set iterator mode.
     *
     * @param int $mode
     *
     * @return FilesystemIterator Provides a fluent interface
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

    /**
     * Get current key.
     */
    public function key(): string
    {
        $filename = $this->globIterator->key();
        // return without namespace prefix and file suffix
        return \substr($filename, $this->prefixLength, -4);
    }

    /**
     * Move forward to next element.
     */
    public function next(): void
    {
        $this->globIterator->next();
    }

    /**
     * Checks if current position is valid.
     */
    public function valid(): bool
    {
        return $this->globIterator->valid();
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind(): void
    {
        $this->globIterator->rewind();
    }
}
