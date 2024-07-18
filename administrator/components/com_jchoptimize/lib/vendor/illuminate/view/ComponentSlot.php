<?php

namespace _JchOptimizeVendor\Illuminate\View;

use _JchOptimizeVendor\Illuminate\Contracts\Support\Htmlable;

class ComponentSlot implements Htmlable
{
    /**
     * The slot attribute bag.
     *
     * @var \Illuminate\View\ComponentAttributeBag
     */
    public $attributes;

    /**
     * The slot contents.
     *
     * @var string
     */
    protected $contents;

    /**
     * Create a new slot instance.
     *
     * @param string $contents
     * @param array  $attributes
     */
    public function __construct($contents = '', $attributes = [])
    {
        $this->contents = $contents;
        $this->withAttributes($attributes);
    }

    /**
     * Get the slot's HTML string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * Set the extra attributes that the slot should make available.
     *
     * @return $this
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = new ComponentAttributeBag($attributes);

        return $this;
    }

    /**
     * Get the slot's HTML string.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->contents;
    }

    /**
     * Determine if the slot is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return '' === $this->contents;
    }

    /**
     * Determine if the slot is not empty.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }
}
