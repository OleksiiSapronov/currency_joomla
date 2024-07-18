<?php

namespace _JchOptimizeVendor\Illuminate\Filesystem;

use _JchOptimizeVendor\Illuminate\Support\ServiceProvider;

class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerNativeFilesystem();
        $this->registerFlysystem();
    }

    /**
     * Register the native filesystem implementation.
     */
    protected function registerNativeFilesystem()
    {
        $this->app->singleton('files', function () {
            return new Filesystem();
        });
    }

    /**
     * Register the driver based filesystem.
     */
    protected function registerFlysystem()
    {
        $this->registerManager();
        $this->app->singleton('filesystem.disk', function ($app) {
            return $app['filesystem']->disk($this->getDefaultDriver());
        });
        $this->app->singleton('filesystem.cloud', function ($app) {
            return $app['filesystem']->disk($this->getCloudDriver());
        });
    }

    /**
     * Register the filesystem manager.
     */
    protected function registerManager()
    {
        $this->app->singleton('filesystem', function ($app) {
            return new FilesystemManager($app);
        });
    }

    /**
     * Get the default file driver.
     *
     * @return string
     */
    protected function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }

    /**
     * Get the default cloud based file driver.
     *
     * @return string
     */
    protected function getCloudDriver()
    {
        return $this->app['config']['filesystems.cloud'];
    }
}
