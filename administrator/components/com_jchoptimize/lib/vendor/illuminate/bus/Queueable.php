<?php

namespace _JchOptimizeVendor\Illuminate\Bus;

use _JchOptimizeVendor\Illuminate\Queue\CallQueuedClosure;
use _JchOptimizeVendor\Illuminate\Support\Arr;

trait Queueable
{
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
     * The name of the connection the chain should be sent to.
     *
     * @var null|string
     */
    public $chainConnection;

    /**
     * The name of the queue the chain should be sent to.
     *
     * @var null|string
     */
    public $chainQueue;

    /**
     * The callbacks to be executed on chain failure.
     *
     * @var null|array
     */
    public $chainCatchCallbacks;

    /**
     * The number of seconds before the job should be made available.
     *
     * @var null|\DateInterval|\DateTimeInterface|int
     */
    public $delay;

    /**
     * Indicates whether the job should be dispatched after all database transactions have committed.
     *
     * @var null|bool
     */
    public $afterCommit;

    /**
     * The middleware the job should be dispatched through.
     *
     * @var array
     */
    public $middleware = [];

    /**
     * The jobs that should run if this job is successful.
     *
     * @var array
     */
    public $chained = [];

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
     * Set the desired connection for the chain.
     *
     * @param null|string $connection
     *
     * @return $this
     */
    public function allOnConnection($connection)
    {
        $this->chainConnection = $connection;
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set the desired queue for the chain.
     *
     * @param null|string $queue
     *
     * @return $this
     */
    public function allOnQueue($queue)
    {
        $this->chainQueue = $queue;
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
     * Indicate that the job should be dispatched after all database transactions have committed.
     *
     * @return $this
     */
    public function afterCommit()
    {
        $this->afterCommit = \true;

        return $this;
    }

    /**
     * Indicate that the job should not wait until database transactions have been committed before dispatching.
     *
     * @return $this
     */
    public function beforeCommit()
    {
        $this->afterCommit = \false;

        return $this;
    }

    /**
     * Specify the middleware the job should be dispatched through.
     *
     * @param array|object $middleware
     *
     * @return $this
     */
    public function through($middleware)
    {
        $this->middleware = Arr::wrap($middleware);

        return $this;
    }

    /**
     * Set the jobs that should run if this job is successful.
     *
     * @param array $chain
     *
     * @return $this
     */
    public function chain($chain)
    {
        $this->chained = collect($chain)->map(function ($job) {
            return $this->serializeJob($job);
        })->all();

        return $this;
    }

    /**
     * Dispatch the next job on the chain.
     */
    public function dispatchNextJobInChain()
    {
        if (!empty($this->chained)) {
            dispatch(tap(\unserialize(\array_shift($this->chained)), function ($next) {
                $next->chained = $this->chained;
                $next->onConnection($next->connection ?: $this->chainConnection);
                $next->onQueue($next->queue ?: $this->chainQueue);
                $next->chainConnection = $this->chainConnection;
                $next->chainQueue = $this->chainQueue;
                $next->chainCatchCallbacks = $this->chainCatchCallbacks;
            }));
        }
    }

    /**
     * Invoke all of the chain's failed job callbacks.
     *
     * @param \Throwable $e
     */
    public function invokeChainCatchCallbacks($e)
    {
        collect($this->chainCatchCallbacks)->each(function ($callback) use ($e) {
            $callback($e);
        });
    }

    /**
     * Serialize a job for queuing.
     *
     * @param mixed $job
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function serializeJob($job)
    {
        if ($job instanceof \Closure) {
            if (!\class_exists(CallQueuedClosure::class)) {
                throw new \RuntimeException('To enable support for closure jobs, please install the illuminate/queue package.');
            }
            $job = CallQueuedClosure::create($job);
        }

        return \serialize($job);
    }
}
