<?php

namespace _JchOptimizeVendor\Illuminate\Events;

use _JchOptimizeVendor\Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use _JchOptimizeVendor\Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('events', function ($app) {
            return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
                return $app->make(QueueFactoryContract::class);
            });
        });
    }
}
