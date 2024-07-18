<?php

namespace _JchOptimizeVendor\Illuminate\Pipeline;

use _JchOptimizeVendor\Illuminate\Contracts\Pipeline\Hub as PipelineHubContract;
use _JchOptimizeVendor\Illuminate\Contracts\Support\DeferrableProvider;
use _JchOptimizeVendor\Illuminate\Support\ServiceProvider;

class PipelineServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(PipelineHubContract::class, Hub::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [PipelineHubContract::class];
    }
}
