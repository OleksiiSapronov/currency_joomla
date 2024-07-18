<?php

namespace _JchOptimizeVendor\Illuminate\Filesystem;

use _JchOptimizeVendor\Aws\S3\S3Client;
use _JchOptimizeVendor\Illuminate\Contracts\Filesystem\Factory as FactoryContract;
use _JchOptimizeVendor\Illuminate\Support\Arr;
use _JchOptimizeVendor\League\Flysystem\Adapter\Ftp as FtpAdapter;
use _JchOptimizeVendor\League\Flysystem\Adapter\Local as LocalAdapter;
use _JchOptimizeVendor\League\Flysystem\AdapterInterface;
use _JchOptimizeVendor\League\Flysystem\AwsS3v3\AwsS3Adapter as S3Adapter;
use _JchOptimizeVendor\League\Flysystem\Cached\CachedAdapter;
use _JchOptimizeVendor\League\Flysystem\Cached\Storage\Memory as MemoryStore;
use _JchOptimizeVendor\League\Flysystem\Filesystem as Flysystem;
use _JchOptimizeVendor\League\Flysystem\FilesystemInterface;
use _JchOptimizeVendor\League\Flysystem\Sftp\SftpAdapter;
use Closure;

/**
 * @mixin \Illuminate\Contracts\Filesystem\Filesystem
 */
class FilesystemManager implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved filesystem drivers.
     *
     * @var array
     */
    protected $disks = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new filesystem manager instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->disk()->{$method}(...$parameters);
    }

    /**
     * Get a filesystem instance.
     *
     * @param null|string $name
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function drive($name = null)
    {
        return $this->disk($name);
    }

    /**
     * Get a filesystem instance.
     *
     * @param null|string $name
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->disks[$name] = $this->get($name);
    }

    /**
     * Get a default cloud filesystem instance.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function cloud()
    {
        $name = $this->getDefaultCloudDriver();

        return $this->disks[$name] = $this->get($name);
    }

    /**
     * Build an on-demand disk.
     *
     * @param array|string $config
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function build($config)
    {
        return $this->resolve('ondemand', \is_array($config) ? $config : ['driver' => 'local', 'root' => $config]);
    }

    /**
     * Create an instance of the local driver.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createLocalDriver(array $config)
    {
        $permissions = $config['permissions'] ?? [];
        $links = ($config['links'] ?? null) === 'skip' ? LocalAdapter::SKIP_LINKS : LocalAdapter::DISALLOW_LINKS;

        return $this->adapt($this->createFlysystem(new LocalAdapter($config['root'], $config['lock'] ?? \LOCK_EX, $links, $permissions), $config));
    }

    /**
     * Create an instance of the ftp driver.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createFtpDriver(array $config)
    {
        return $this->adapt($this->createFlysystem(new FtpAdapter($config), $config));
    }

    /**
     * Create an instance of the sftp driver.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function createSftpDriver(array $config)
    {
        return $this->adapt($this->createFlysystem(new SftpAdapter($config), $config));
    }

    /**
     * Create an instance of the Amazon S3 driver.
     *
     * @return \Illuminate\Contracts\Filesystem\Cloud
     */
    public function createS3Driver(array $config)
    {
        $s3Config = $this->formatS3Config($config);
        $root = $s3Config['root'] ?? null;
        $options = $config['options'] ?? [];
        $streamReads = $config['stream_reads'] ?? \false;

        return $this->adapt($this->createFlysystem(new S3Adapter(new S3Client($s3Config), $s3Config['bucket'], $root, $options, $streamReads), $config));
    }

    /**
     * Set the given disk instance.
     *
     * @param string $name
     * @param mixed  $disk
     *
     * @return $this
     */
    public function set($name, $disk)
    {
        $this->disks[$name] = $disk;

        return $this;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }

    /**
     * Get the default cloud driver name.
     *
     * @return string
     */
    public function getDefaultCloudDriver()
    {
        return $this->app['config']['filesystems.cloud'] ?? 's3';
    }

    /**
     * Unset the given disk instances.
     *
     * @param array|string $disk
     *
     * @return $this
     */
    public function forgetDisk($disk)
    {
        foreach ((array) $disk as $diskName) {
            unset($this->disks[$diskName]);
        }

        return $this;
    }

    /**
     * Disconnect the given disk and remove from local cache.
     *
     * @param null|string $name
     */
    public function purge($name = null)
    {
        $name = $name ?? $this->getDefaultDriver();
        unset($this->disks[$name]);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string $driver
     *
     * @return $this
     */
    public function extend($driver, \Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Set the application instance used by the manager.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return $this
     */
    public function setApplication($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Attempt to get the disk from the local cache.
     *
     * @param string $name
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function get($name)
    {
        return $this->disks[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given disk.
     *
     * @param string     $name
     * @param null|array $config
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name, $config = null)
    {
        $config = $config ?? $this->getConfig($name);
        if (empty($config['driver'])) {
            throw new \InvalidArgumentException("Disk [{$name}] does not have a configured driver.");
        }
        $name = $config['driver'];
        if (isset($this->customCreators[$name])) {
            return $this->callCustomCreator($config);
        }
        $driverMethod = 'create'.\ucfirst($name).'Driver';
        if (!\method_exists($this, $driverMethod)) {
            throw new \InvalidArgumentException("Driver [{$name}] is not supported.");
        }

        return $this->{$driverMethod}($config);
    }

    /**
     * Call a custom driver creator.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function callCustomCreator(array $config)
    {
        $driver = $this->customCreators[$config['driver']]($this->app, $config);
        if ($driver instanceof FilesystemInterface) {
            return $this->adapt($driver);
        }

        return $driver;
    }

    /**
     * Format the given S3 configuration with the default options.
     *
     * @return array
     */
    protected function formatS3Config(array $config)
    {
        $config += ['version' => 'latest'];
        if (!empty($config['key']) && !empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return $config;
    }

    /**
     * Create a Flysystem instance with the given adapter.
     *
     * @param \League\Flysystem\AdapterInterface $adapter
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function createFlysystem(AdapterInterface $adapter, array $config)
    {
        $cache = Arr::pull($config, 'cache');
        $config = Arr::only($config, ['visibility', 'disable_asserts', 'url', 'temporary_url']);
        if ($cache) {
            $adapter = new CachedAdapter($adapter, $this->createCacheStore($cache));
        }

        return new Flysystem($adapter, \count($config) > 0 ? $config : null);
    }

    /**
     * Create a cache store instance.
     *
     * @param mixed $config
     *
     * @return \League\Flysystem\Cached\CacheInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function createCacheStore($config)
    {
        if (\true === $config) {
            return new MemoryStore();
        }

        return new Cache($this->app['cache']->store($config['store']), $config['prefix'] ?? 'flysystem', $config['expire'] ?? null);
    }

    /**
     * Adapt the filesystem implementation.
     *
     * @param \League\Flysystem\FilesystemInterface $filesystem
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function adapt(FilesystemInterface $filesystem)
    {
        return new FilesystemAdapter($filesystem);
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["filesystems.disks.{$name}"] ?: [];
    }
}
