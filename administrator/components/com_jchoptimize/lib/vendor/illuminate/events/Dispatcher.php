<?php

namespace _JchOptimizeVendor\Illuminate\Events;

use _JchOptimizeVendor\Illuminate\Container\Container;
use _JchOptimizeVendor\Illuminate\Contracts\Broadcasting\Factory as BroadcastFactory;
use _JchOptimizeVendor\Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use _JchOptimizeVendor\Illuminate\Contracts\Container\Container as ContainerContract;
use _JchOptimizeVendor\Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use _JchOptimizeVendor\Illuminate\Contracts\Queue\ShouldBeEncrypted;
use _JchOptimizeVendor\Illuminate\Contracts\Queue\ShouldQueue;
use _JchOptimizeVendor\Illuminate\Support\Arr;
use _JchOptimizeVendor\Illuminate\Support\Str;
use _JchOptimizeVendor\Illuminate\Support\Traits\Macroable;
use _JchOptimizeVendor\Illuminate\Support\Traits\ReflectsClosures;

use function _JchOptimizeVendor\tap;

class Dispatcher implements DispatcherContract
{
    use Macroable;
    use ReflectsClosures;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The registered event listeners.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * The wildcard listeners.
     *
     * @var array
     */
    protected $wildcards = [];

    /**
     * The cached wildcard listeners.
     *
     * @var array
     */
    protected $wildcardsCache = [];

    /**
     * The queue resolver instance.
     *
     * @var callable
     */
    protected $queueResolver;

    /**
     * Create a new event dispatcher instance.
     *
     * @param null|\Illuminate\Contracts\Container\Container $container
     */
    public function __construct(ContainerContract $container = null)
    {
        $this->container = $container ?: new Container();
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param array|\Closure|string      $events
     * @param null|array|\Closure|string $listener
     */
    public function listen($events, $listener = null)
    {
        if ($events instanceof \Closure) {
            return collect($this->firstClosureParameterTypes($events))->each(function ($event) use ($events) {
                $this->listen($event, $events);
            });
        } elseif ($events instanceof QueuedClosure) {
            return collect($this->firstClosureParameterTypes($events->closure))->each(function ($event) use ($events) {
                $this->listen($event, $events->resolve());
            });
        } elseif ($listener instanceof QueuedClosure) {
            $listener = $listener->resolve();
        }
        foreach ((array) $events as $event) {
            if (Str::contains($event, '*')) {
                $this->setupWildcardListen($event, $listener);
            } else {
                $this->listeners[$event][] = $this->makeListener($listener);
            }
        }
    }

    /**
     * Determine if a given event has listeners.
     *
     * @param string $eventName
     *
     * @return bool
     */
    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]) || isset($this->wildcards[$eventName]) || $this->hasWildcardListeners($eventName);
    }

    /**
     * Determine if the given event has any wildcard listeners.
     *
     * @param string $eventName
     *
     * @return bool
     */
    public function hasWildcardListeners($eventName)
    {
        foreach ($this->wildcards as $key => $listeners) {
            if (Str::is($key, $eventName)) {
                return \true;
            }
        }

        return \false;
    }

    /**
     * Register an event and payload to be fired later.
     *
     * @param string $event
     * @param array  $payload
     */
    public function push($event, $payload = [])
    {
        $this->listen($event.'_pushed', function () use ($event, $payload) {
            $this->dispatch($event, $payload);
        });
    }

    /**
     * Flush a set of pushed events.
     *
     * @param string $event
     */
    public function flush($event)
    {
        $this->dispatch($event.'_pushed');
    }

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param object|string $subscriber
     */
    public function subscribe($subscriber)
    {
        $subscriber = $this->resolveSubscriber($subscriber);
        $events = $subscriber->subscribe($this);
        if (\is_array($events)) {
            foreach ($events as $event => $listeners) {
                foreach (Arr::wrap($listeners) as $listener) {
                    if (\is_string($listener) && \method_exists($subscriber, $listener)) {
                        $this->listen($event, [\get_class($subscriber), $listener]);

                        continue;
                    }
                    $this->listen($event, $listener);
                }
            }
        }
    }

    /**
     * Fire an event until the first non-null response is returned.
     *
     * @param object|string $event
     * @param mixed         $payload
     *
     * @return null|array
     */
    public function until($event, $payload = [])
    {
        return $this->dispatch($event, $payload, \true);
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param object|string $event
     * @param mixed         $payload
     * @param bool          $halt
     *
     * @return null|array
     */
    public function dispatch($event, $payload = [], $halt = \false)
    {
        // When the given "event" is actually an object we will assume it is an event
        // object and use the class as the event name and this event itself as the
        // payload to the handler, which makes object based events quite simple.
        [$event, $payload] = $this->parseEventAndPayload($event, $payload);
        if ($this->shouldBroadcast($payload)) {
            $this->broadcastEvent($payload[0]);
        }
        $responses = [];
        foreach ($this->getListeners($event) as $listener) {
            $response = $listener($event, $payload);
            // If a response is returned from the listener and event halting is enabled
            // we will just return this response, and not call the rest of the event
            // listeners. Otherwise we will add the response on the response list.
            if ($halt && !\is_null($response)) {
                return $response;
            }
            // If a boolean false is returned from a listener, we will stop propagating
            // the event to any further listeners down in the chain, else we keep on
            // looping through the listeners and firing every one in our sequence.
            if (\false === $response) {
                break;
            }
            $responses[] = $response;
        }

        return $halt ? null : $responses;
    }

    /**
     * Get all of the listeners for a given event name.
     *
     * @param string $eventName
     *
     * @return array
     */
    public function getListeners($eventName)
    {
        $listeners = $this->listeners[$eventName] ?? [];
        $listeners = \array_merge($listeners, $this->wildcardsCache[$eventName] ?? $this->getWildcardListeners($eventName));

        return \class_exists($eventName, \false) ? $this->addInterfaceListeners($eventName, $listeners) : $listeners;
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param array|\Closure|string $listener
     * @param bool                  $wildcard
     *
     * @return \Closure
     */
    public function makeListener($listener, $wildcard = \false)
    {
        if (\is_string($listener)) {
            return $this->createClassListener($listener, $wildcard);
        }
        if (\is_array($listener) && isset($listener[0]) && \is_string($listener[0])) {
            return $this->createClassListener($listener, $wildcard);
        }

        return function ($event, $payload) use ($listener, $wildcard) {
            if ($wildcard) {
                return $listener($event, $payload);
            }

            return $listener(...\array_values($payload));
        };
    }

    /**
     * Create a class based listener using the IoC container.
     *
     * @param string $listener
     * @param bool   $wildcard
     *
     * @return \Closure
     */
    public function createClassListener($listener, $wildcard = \false)
    {
        return function ($event, $payload) use ($listener, $wildcard) {
            if ($wildcard) {
                return \call_user_func($this->createClassCallable($listener), $event, $payload);
            }
            $callable = $this->createClassCallable($listener);

            return $callable(...\array_values($payload));
        };
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param string $event
     */
    public function forget($event)
    {
        if (Str::contains($event, '*')) {
            unset($this->wildcards[$event]);
        } else {
            unset($this->listeners[$event]);
        }
        foreach ($this->wildcardsCache as $key => $listeners) {
            if (Str::is($event, $key)) {
                unset($this->wildcardsCache[$key]);
            }
        }
    }

    /**
     * Forget all of the pushed listeners.
     */
    public function forgetPushed()
    {
        foreach ($this->listeners as $key => $value) {
            if (Str::endsWith($key, '_pushed')) {
                $this->forget($key);
            }
        }
    }

    /**
     * Set the queue resolver implementation.
     *
     * @return $this
     */
    public function setQueueResolver(callable $resolver)
    {
        $this->queueResolver = $resolver;

        return $this;
    }

    /**
     * Setup a wildcard listener callback.
     *
     * @param string          $event
     * @param \Closure|string $listener
     */
    protected function setupWildcardListen($event, $listener)
    {
        $this->wildcards[$event][] = $this->makeListener($listener, \true);
        $this->wildcardsCache = [];
    }

    /**
     * Resolve the subscriber instance.
     *
     * @param object|string $subscriber
     *
     * @return mixed
     */
    protected function resolveSubscriber($subscriber)
    {
        if (\is_string($subscriber)) {
            return $this->container->make($subscriber);
        }

        return $subscriber;
    }

    /**
     * Parse the given event and payload and prepare them for dispatching.
     *
     * @param mixed $event
     * @param mixed $payload
     *
     * @return array
     */
    protected function parseEventAndPayload($event, $payload)
    {
        if (\is_object($event)) {
            [$payload, $event] = [[$event], \get_class($event)];
        }

        return [$event, Arr::wrap($payload)];
    }

    /**
     * Determine if the payload has a broadcastable event.
     *
     * @return bool
     */
    protected function shouldBroadcast(array $payload)
    {
        return isset($payload[0]) && $payload[0] instanceof ShouldBroadcast && $this->broadcastWhen($payload[0]);
    }

    /**
     * Check if the event should be broadcasted by the condition.
     *
     * @param mixed $event
     *
     * @return bool
     */
    protected function broadcastWhen($event)
    {
        return \method_exists($event, 'broadcastWhen') ? $event->broadcastWhen() : \true;
    }

    /**
     * Broadcast the given event class.
     *
     * @param \Illuminate\Contracts\Broadcasting\ShouldBroadcast $event
     */
    protected function broadcastEvent($event)
    {
        $this->container->make(BroadcastFactory::class)->queue($event);
    }

    /**
     * Get the wildcard listeners for the event.
     *
     * @param string $eventName
     *
     * @return array
     */
    protected function getWildcardListeners($eventName)
    {
        $wildcards = [];
        foreach ($this->wildcards as $key => $listeners) {
            if (Str::is($key, $eventName)) {
                $wildcards = \array_merge($wildcards, $listeners);
            }
        }

        return $this->wildcardsCache[$eventName] = $wildcards;
    }

    /**
     * Add the listeners for the event's interfaces to the given array.
     *
     * @param string $eventName
     *
     * @return array
     */
    protected function addInterfaceListeners($eventName, array $listeners = [])
    {
        foreach (\class_implements($eventName) as $interface) {
            if (isset($this->listeners[$interface])) {
                foreach ($this->listeners[$interface] as $names) {
                    $listeners = \array_merge($listeners, (array) $names);
                }
            }
        }

        return $listeners;
    }

    /**
     * Create the class based event callable.
     *
     * @param array|string $listener
     *
     * @return callable
     */
    protected function createClassCallable($listener)
    {
        [$class, $method] = \is_array($listener) ? $listener : $this->parseClassCallable($listener);
        if (!\method_exists($class, $method)) {
            $method = '__invoke';
        }
        if ($this->handlerShouldBeQueued($class)) {
            return $this->createQueuedHandlerCallable($class, $method);
        }
        $listener = $this->container->make($class);

        return $this->handlerShouldBeDispatchedAfterDatabaseTransactions($listener) ? $this->createCallbackForListenerRunningAfterCommits($listener, $method) : [$listener, $method];
    }

    /**
     * Parse the class listener into class and method.
     *
     * @param string $listener
     *
     * @return array
     */
    protected function parseClassCallable($listener)
    {
        return Str::parseCallback($listener, 'handle');
    }

    /**
     * Determine if the event handler class should be queued.
     *
     * @param string $class
     *
     * @return bool
     */
    protected function handlerShouldBeQueued($class)
    {
        try {
            return (new \ReflectionClass($class))->implementsInterface(ShouldQueue::class);
        } catch (\Exception $e) {
            return \false;
        }
    }

    /**
     * Create a callable for putting an event handler on the queue.
     *
     * @param string $class
     * @param string $method
     *
     * @return \Closure
     */
    protected function createQueuedHandlerCallable($class, $method)
    {
        return function () use ($class, $method) {
            $arguments = \array_map(function ($a) {
                return \is_object($a) ? clone $a : $a;
            }, \func_get_args());
            if ($this->handlerWantsToBeQueued($class, $arguments)) {
                $this->queueHandler($class, $method, $arguments);
            }
        };
    }

    /**
     * Determine if the given event handler should be dispatched after all database transactions have committed.
     *
     * @param mixed|object $listener
     *
     * @return bool
     */
    protected function handlerShouldBeDispatchedAfterDatabaseTransactions($listener)
    {
        return ($listener->afterCommit ?? null) && $this->container->bound('db.transactions');
    }

    /**
     * Create a callable for dispatching a listener after database transactions.
     *
     * @param mixed  $listener
     * @param string $method
     *
     * @return \Closure
     */
    protected function createCallbackForListenerRunningAfterCommits($listener, $method)
    {
        return function () use ($method, $listener) {
            $payload = \func_get_args();
            $this->container->make('db.transactions')->addCallback(function () use ($listener, $method, $payload) {
                $listener->{$method}(...$payload);
            });
        };
    }

    /**
     * Determine if the event handler wants to be queued.
     *
     * @param string $class
     * @param array  $arguments
     *
     * @return bool
     */
    protected function handlerWantsToBeQueued($class, $arguments)
    {
        $instance = $this->container->make($class);
        if (\method_exists($instance, 'shouldQueue')) {
            return $instance->shouldQueue($arguments[0]);
        }

        return \true;
    }

    /**
     * Queue the handler class.
     *
     * @param string $class
     * @param string $method
     * @param array  $arguments
     */
    protected function queueHandler($class, $method, $arguments)
    {
        [$listener, $job] = $this->createListenerAndJob($class, $method, $arguments);
        $connection = $this->resolveQueue()->connection(\method_exists($listener, 'viaConnection') ? $listener->viaConnection() : $listener->connection ?? null);
        $queue = \method_exists($listener, 'viaQueue') ? $listener->viaQueue() : $listener->queue ?? null;
        isset($listener->delay) ? $connection->laterOn($queue, $listener->delay, $job) : $connection->pushOn($queue, $job);
    }

    /**
     * Create the listener and job for a queued listener.
     *
     * @param string $class
     * @param string $method
     * @param array  $arguments
     *
     * @return array
     */
    protected function createListenerAndJob($class, $method, $arguments)
    {
        $listener = (new \ReflectionClass($class))->newInstanceWithoutConstructor();

        return [$listener, $this->propagateListenerOptions($listener, new CallQueuedListener($class, $method, $arguments))];
    }

    /**
     * Propagate listener options to the job.
     *
     * @param mixed $listener
     * @param mixed $job
     *
     * @return mixed
     */
    protected function propagateListenerOptions($listener, $job)
    {
        return tap($job, function ($job) use ($listener) {
            $job->afterCommit = \property_exists($listener, 'afterCommit') ? $listener->afterCommit : null;
            $job->backoff = \method_exists($listener, 'backoff') ? $listener->backoff() : $listener->backoff ?? null;
            $job->maxExceptions = $listener->maxExceptions ?? null;
            $job->retryUntil = \method_exists($listener, 'retryUntil') ? $listener->retryUntil() : null;
            $job->shouldBeEncrypted = $listener instanceof ShouldBeEncrypted;
            $job->timeout = $listener->timeout ?? null;
            $job->tries = $listener->tries ?? null;
            $job->through(\array_merge(\method_exists($listener, 'middleware') ? $listener->middleware() : [], $listener->middleware ?? []));
        });
    }

    /**
     * Get the queue implementation from the resolver.
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    protected function resolveQueue()
    {
        return \call_user_func($this->queueResolver);
    }
}
