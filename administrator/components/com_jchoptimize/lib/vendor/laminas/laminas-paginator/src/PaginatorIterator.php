<?php

namespace _JchOptimizeVendor\Laminas\Paginator;

use Iterator;
use OuterIterator;

/**
 * Class allowing for the continuous iteration of a Laminas\Paginator\Paginator instance.
 * Useful for representing remote paginated data sources as a single Iterator.
 */
class PaginatorIterator implements \OuterIterator
{
    /**
     * Internal Paginator for iteration.
     *
     * @var Paginator
     */
    protected $paginator;

    /**
     * Value for valid method.
     *
     * @var bool
     */
    protected $valid = \true;

    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Return the current element.
     *
     * @see http://php.net/manual/en/iterator.current.php
     *
     * @return mixed can return any type
     */
    public function current()
    {
        return $this->getInnerIterator()->current();
    }

    /**
     * Move forward to next element.
     *
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        $innerIterator = $this->getInnerIterator();
        $innerIterator->next();
        if ($innerIterator->valid()) {
            return;
        }
        $page = $this->paginator->getCurrentPageNumber();
        $nextPage = $page + 1;
        $this->paginator->setCurrentPageNumber($nextPage);
        $page = $this->paginator->getCurrentPageNumber();
        if ($page !== $nextPage) {
            $this->valid = \false;
        }
    }

    /**
     * Return the key of the current element.
     *
     * @see http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure
     */
    public function key()
    {
        $innerKey = $this->getInnerIterator()->key();
        \assert(\is_int($innerKey));
        ++$innerKey;
        // Laminas\Paginator\Paginator normalizes 0 to 1
        $this->paginator->getCurrentPageNumber();

        return $this->paginator->getAbsoluteItemNumber($innerKey, $this->paginator->getCurrentPageNumber()) - 1;
    }

    /**
     * Checks if current position is valid.
     *
     * @see http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     */
    public function valid()
    {
        if (\count($this->paginator) < 1) {
            $this->valid = \false;
        }

        return $this->valid;
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        $this->paginator->setCurrentPageNumber(1);
        $this->valid = \true;
    }

    /**
     * Returns the inner iterator for the current entry.
     *
     * @see http://php.net/manual/en/outeriterator.getinneriterator.php
     *
     * @return \Iterator the inner iterator for the current entry
     */
    public function getInnerIterator()
    {
        return $this->paginator->getCurrentItems();
    }
}
