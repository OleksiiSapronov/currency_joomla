<?php

namespace _JchOptimizeVendor\Illuminate\Contracts\Container;

interface ContextualBindingBuilder
{
    /**
     * Define the abstract target that depends on the context.
     *
     * @param string $abstract
     *
     * @return $this
     */
    public function needs($abstract);

    /**
     * Define the implementation for the contextual binding.
     *
     * @param array|\Closure|string $implementation
     */
    public function give($implementation);

    /**
     * Define tagged services to be used as the implementation for the contextual binding.
     *
     * @param string $tag
     */
    public function giveTagged($tag);
}
