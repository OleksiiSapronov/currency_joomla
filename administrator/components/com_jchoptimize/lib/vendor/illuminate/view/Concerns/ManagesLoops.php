<?php

namespace _JchOptimizeVendor\Illuminate\View\Concerns;

use _JchOptimizeVendor\Illuminate\Support\Arr;

trait ManagesLoops
{
    /**
     * The stack of in-progress loops.
     *
     * @var array
     */
    protected $loopsStack = [];

    /**
     * Add new loop to the stack.
     *
     * @param array|\Countable $data
     */
    public function addLoop($data)
    {
        $length = \is_array($data) || $data instanceof \Countable ? \count($data) : null;
        $parent = Arr::last($this->loopsStack);
        $this->loopsStack[] = ['iteration' => 0, 'index' => 0, 'remaining' => $length ?? null, 'count' => $length, 'first' => \true, 'last' => isset($length) ? 1 == $length : null, 'odd' => \false, 'even' => \true, 'depth' => \count($this->loopsStack) + 1, 'parent' => $parent ? (object) $parent : null];
    }

    /**
     * Increment the top loop's indices.
     */
    public function incrementLoopIndices()
    {
        $loop = $this->loopsStack[$index = \count($this->loopsStack) - 1];
        $this->loopsStack[$index] = \array_merge($this->loopsStack[$index], ['iteration' => $loop['iteration'] + 1, 'index' => $loop['iteration'], 'first' => 0 == $loop['iteration'], 'odd' => !$loop['odd'], 'even' => !$loop['even'], 'remaining' => isset($loop['count']) ? $loop['remaining'] - 1 : null, 'last' => isset($loop['count']) ? $loop['count'] - 1 == $loop['iteration'] : null]);
    }

    /**
     * Pop a loop from the top of the loop stack.
     */
    public function popLoop()
    {
        \array_pop($this->loopsStack);
    }

    /**
     * Get an instance of the last loop in the stack.
     *
     * @return null|\stdClass
     */
    public function getLastLoop()
    {
        if ($last = Arr::last($this->loopsStack)) {
            return (object) $last;
        }
    }

    /**
     * Get the entire loop stack.
     *
     * @return array
     */
    public function getLoopStack()
    {
        return $this->loopsStack;
    }
}
