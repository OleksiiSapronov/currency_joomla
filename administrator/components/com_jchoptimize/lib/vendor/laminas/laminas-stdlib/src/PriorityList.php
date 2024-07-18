<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

use ReturnTypeWillChange;

class PriorityList implements \Iterator, \Countable
{
    public const EXTR_DATA = 0x1;
    public const EXTR_PRIORITY = 0x2;
    public const EXTR_BOTH = 0x3;

    /**
     * Internal list of all items.
     *
     * @var array[]
     */
    protected $items = [];

    /**
     * Serial assigned to items to preserve LIFO.
     *
     * @var int
     */
    protected $serial = 0;
    // phpcs:disable WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCapsProperty
    /**
     * Serial order mode.
     *
     * @var int
     */
    protected $isLIFO = 1;
    // phpcs:enable
    /**
     * Internal counter to avoid usage of count().
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Whether the list was already sorted.
     *
     * @var bool
     */
    protected $sorted = \false;

    /**
     * Insert a new item.
     *
     * @param string $name
     * @param mixed  $value
     * @param int    $priority
     */
    public function insert($name, $value, $priority = 0)
    {
        if (!isset($this->items[$name])) {
            ++$this->count;
        }
        $this->sorted = \false;
        $this->items[$name] = ['data' => $value, 'priority' => (int) $priority, 'serial' => $this->serial++];
    }

    /**
     * @param string $name
     * @param int    $priority
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setPriority($name, $priority)
    {
        if (!isset($this->items[$name])) {
            throw new \Exception("item {$name} not found");
        }
        $this->items[$name]['priority'] = (int) $priority;
        $this->sorted = \false;

        return $this;
    }

    /**
     * Remove a item.
     *
     * @param string $name
     */
    public function remove($name)
    {
        if (isset($this->items[$name])) {
            --$this->count;
        }
        unset($this->items[$name]);
    }

    /**
     * Remove all items.
     */
    public function clear()
    {
        $this->items = [];
        $this->serial = 0;
        $this->count = 0;
        $this->sorted = \false;
    }

    /**
     * Get a item.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        if (!isset($this->items[$name])) {
            return;
        }

        return $this->items[$name]['data'];
    }

    /**
     * Get/Set serial order mode.
     *
     * @param null|bool $flag
     *
     * @return bool
     */
    public function isLIFO($flag = null)
    {
        if (null !== $flag) {
            $isLifo = \true === $flag ? 1 : -1;
            if ($isLifo !== $this->isLIFO) {
                $this->isLIFO = $isLifo;
                $this->sorted = \false;
            }
        }

        return 1 === $this->isLIFO;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->sort();
        \reset($this->items);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        $this->sorted || $this->sort();
        $node = \current($this->items);

        return $node ? $node['data'] : \false;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        $this->sorted || $this->sort();

        return \key($this->items);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $node = \next($this->items);

        return $node ? $node['data'] : \false;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return \false !== \current($this->items);
    }

    /**
     * @return self
     */
    public function getIterator()
    {
        return clone $this;
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return $this->count;
    }

    /**
     * Return list as array.
     *
     * @param int $flag
     *
     * @return array
     */
    public function toArray($flag = self::EXTR_DATA)
    {
        $this->sort();
        if (self::EXTR_BOTH === $flag) {
            return $this->items;
        }

        return \array_map(static fn ($item) => self::EXTR_PRIORITY === $flag ? $item['priority'] : $item['data'], $this->items);
    }

    /**
     * Sort all items.
     */
    protected function sort()
    {
        if (!$this->sorted) {
            \uasort($this->items, [$this, 'compare']);
            $this->sorted = \true;
        }
    }

    /**
     * Compare the priority of two items.
     *
     * @return int
     */
    protected function compare(array $item1, array $item2)
    {
        return $item1['priority'] === $item2['priority'] ? ($item1['serial'] > $item2['serial'] ? -1 : 1) * $this->isLIFO : ($item1['priority'] > $item2['priority'] ? -1 : 1);
    }
}
