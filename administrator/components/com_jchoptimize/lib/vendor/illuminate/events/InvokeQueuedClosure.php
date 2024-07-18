<?php

namespace _JchOptimizeVendor\Illuminate\Events;

class InvokeQueuedClosure
{
    /**
     * Handle the event.
     *
     * @param \Laravel\SerializableClosure\SerializableClosure $closure
     */
    public function handle($closure, array $arguments)
    {
        \call_user_func($closure->getClosure(), ...$arguments);
    }

    /**
     * Handle a job failure.
     *
     * @param \Laravel\SerializableClosure\SerializableClosure $closure
     * @param \Throwable                                       $exception
     */
    public function failed($closure, array $arguments, array $catchCallbacks, $exception)
    {
        $arguments[] = $exception;
        collect($catchCallbacks)->each->__invoke(...$arguments);
    }
}
