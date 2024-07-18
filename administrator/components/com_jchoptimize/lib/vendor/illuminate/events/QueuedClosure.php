<?php

namespace _JchOptimizeVendor\Illuminate\Events;

use _JchOptimizeVendor\Illuminate\Queue\SerializableClosureFactory;
use Closure;

class QueuedClosure
{
    /**
     * The underlying Closure.
     *
     * @var \Closure
     */
    public $closure;

    /**
     * The name of the connection the job should be sent to.
     *
     * @var null|string
     */
    public $connection;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var null|string
     */
    public $queue;

    /**
     * The number of seconds before the job should be made available.
     *
     * @var null|\DateInterval|\DateTimeInterface|int
     */
    public $delay;

    /**
     * All of the "catch" callbacks for the queued closure.
     *
     * @var array
     */
    public $catchCallbacks = [];

    /**
     * Create a new queued closure event listener resolver.
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * Set the desired connection for the job.
     *
     * @param null|string $connection
     *
     * @return $this
     */
    public function onConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set the desired queue for the job.
     *
     * @param null|string $queue
     *
     * @return $this
     */
    public function onQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Set the desired delay for the job.
     *
     * @param null|\DateInterval|\DateTimeInterface|int $delay
     *
     * @return $this
     */
    public function delay($delay)
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Specify a callback that should be invoked if the queued listener job fails.
     *
     * @return $this
     */
    public function catch(\Closure $closure)
    {
        $this->catchCallbacks[] = $closure;

        return $this;
    }

    /**
     * Resolve the actual event listener callback.
     *
     * @return \Closure
     */
    public function resolve()
    {
        return function (...$arguments) {
            dispatch(new CallQueuedListener(InvokeQueuedClosure::class, 'handle', ['closure' => SerializableClosureFactory::make($this->closure), 'arguments' => $arguments, 'catch' => collect($this->catchCallbacks)->map(function ($callback) {
                return SerializableClosureFactory::make($callback);
            })->all()]))->onConnection($this->connection)->onQueue($this->queue)->delay($this->delay);
        };
    }
}
