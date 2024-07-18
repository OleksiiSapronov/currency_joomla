<?php

namespace _JchOptimizeVendor\Illuminate\Events;

use Closure;

if (!\function_exists('_JchOptimizeVendor\\Illuminate\\Events\\queueable')) {
    /**
     * Create a new queued Closure event listener.
     *
     * @return \Illuminate\Events\QueuedClosure
     */
    function queueable(\Closure $closure)
    {
        return new QueuedClosure($closure);
    }
}
