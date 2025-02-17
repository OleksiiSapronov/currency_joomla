<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter;

use _JchOptimizeVendor\Laminas\Cache\Exception;
use _JchOptimizeVendor\Laminas\Stdlib\ArrayUtils;
use Memcached as MemcachedResource;

use function constant;

/**
 * This is a resource manager for memcached.
 */
class MemcachedResourceManager
{
    /**
     * Registered resources.
     *
     * @var array
     */
    protected $resources = [];

    /**
     * Get servers.
     *
     * @param string $id
     *
     * @return array array('host' => <host>, 'port' => <port>, 'weight' => <weight>)
     *
     * @throws Exception\RuntimeException
     */
    public function getServers($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }
        $resource = &$this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            return $resource->getServerList();
        }

        return $resource['servers'];
    }

    /**
     * Check if a resource exists.
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasResource($id)
    {
        return isset($this->resources[$id]);
    }

    /**
     * Gets a memcached resource.
     *
     * @param string $id
     *
     * @return MemcachedResource
     *
     * @throws Exception\RuntimeException
     */
    public function getResource($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }
        $resource = $this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            return $resource;
        }
        if ('' !== $resource['persistent_id']) {
            $memc = new MemcachedResource($resource['persistent_id']);
        } else {
            $memc = new MemcachedResource();
        }
        if (\method_exists($memc, 'setOptions')) {
            $memc->setOptions($resource['lib_options']);
        } else {
            foreach ($resource['lib_options'] as $k => $v) {
                $memc->setOption($k, $v);
            }
        }
        // merge and add servers (with persistence id servers could be added already)
        $servers = \array_udiff($resource['servers'], $memc->getServerList(), [$this, 'compareServers']);
        if ($servers) {
            $memc->addServers(\array_values(\array_map('array_values', $servers)));
        }
        // buffer and return
        $this->resources[$id] = $memc;

        return $memc;
    }

    /**
     * Set a resource.
     *
     * @param string                               $id
     * @param array|MemcachedResource|\Traversable $resource
     *
     * @return MemcachedResourceManager Provides a fluent interface
     */
    public function setResource($id, $resource)
    {
        $id = (string) $id;
        if (!$resource instanceof MemcachedResource) {
            if ($resource instanceof \Traversable) {
                $resource = ArrayUtils::iteratorToArray($resource);
            } elseif (!\is_array($resource)) {
                throw new Exception\InvalidArgumentException('Resource must be an instance of Memcached or an array or Traversable');
            }
            $resource = \array_merge(['persistent_id' => '', 'lib_options' => [], 'servers' => []], $resource);
            // normalize and validate params
            $this->normalizePersistentId($resource['persistent_id']);
            $this->normalizeLibOptions($resource['lib_options']);
            $this->normalizeServers($resource['servers']);
        }
        $this->resources[$id] = $resource;

        return $this;
    }

    /**
     * Remove a resource.
     *
     * @param string $id
     *
     * @return MemcachedResourceManager Provides a fluent interface
     */
    public function removeResource($id)
    {
        unset($this->resources[$id]);

        return $this;
    }

    /**
     * Set the persistent id.
     *
     * @param string $id
     * @param string $persistentId
     *
     * @return MemcachedResourceManager Provides a fluent interface
     *
     * @throws Exception\RuntimeException
     */
    public function setPersistentId($id, $persistentId)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, ['persistent_id' => $persistentId]);
        }
        $resource = &$this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            throw new Exception\RuntimeException("Can't change persistent id of resource {$id} after instanziation");
        }
        $this->normalizePersistentId($persistentId);
        $resource['persistent_id'] = $persistentId;

        return $this;
    }

    /**
     * Get the persistent id.
     *
     * @param string $id
     *
     * @return string
     *
     * @throws Exception\RuntimeException
     */
    public function getPersistentId($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }
        $resource = &$this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            throw new Exception\RuntimeException("Can't get persistent id of an instantiated memcached resource");
        }

        return $resource['persistent_id'];
    }

    /**
     * Set Libmemcached options.
     *
     * @param string $id
     *
     * @return MemcachedResourceManager Provides a fluent interface
     */
    public function setLibOptions($id, array $libOptions)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, ['lib_options' => $libOptions]);
        }
        $this->normalizeLibOptions($libOptions);
        $resource = &$this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            if (\method_exists($resource, 'setOptions')) {
                $resource->setOptions($libOptions);
            } else {
                foreach ($libOptions as $key => $value) {
                    $resource->setOption($key, $value);
                }
            }
        } else {
            $resource['lib_options'] = $libOptions;
        }

        return $this;
    }

    /**
     * Get Libmemcached options.
     *
     * @param string $id
     *
     * @return array
     *
     * @throws Exception\RuntimeException
     */
    public function getLibOptions($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }
        $resource = &$this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            $libOptions = [];
            $reflection = new \ReflectionClass('Memcached');
            $constants = $reflection->getConstants();
            foreach ($constants as $constName => $constValue) {
                if (0 === \strpos($constName, 'OPT_')) {
                    $libOptions[$constValue] = $resource->getOption($constValue);
                }
            }

            return $libOptions;
        }

        return $resource['lib_options'];
    }

    /**
     * Set one Libmemcached option.
     *
     * @param string     $id
     * @param int|string $key
     * @param mixed      $value
     *
     * @return MemcachedResourceManager Fluent interface
     */
    public function setLibOption($id, $key, $value)
    {
        return $this->setLibOptions($id, [$key => $value]);
    }

    /**
     * Get one Libmemcached option.
     *
     * @param string     $id
     * @param int|string $key
     *
     * @return mixed
     *
     * @throws Exception\RuntimeException
     */
    public function getLibOption($id, $key)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }
        $this->normalizeLibOptionKey($key);
        $resource = &$this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            return $resource->getOption($key);
        }

        return $resource['lib_options'][$key] ?? null;
    }

    /**
     * Set servers.
     *
     * $servers can be an array list or a comma separated list of servers.
     * One server in the list can be descripted as follows:
     * - URI:   [tcp://]<host>[:<port>][?weight=<weight>]
     * - Assoc: array('host' => <host>[, 'port' => <port>][, 'weight' => <weight>])
     * - List:  array(<host>[, <port>][, <weight>])
     *
     * @param string       $id
     * @param array|string $servers
     *
     * @return MemcachedResourceManager Provides a fluent interface
     */
    public function setServers($id, $servers)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, ['servers' => $servers]);
        }
        $this->normalizeServers($servers);
        $resource = &$this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            // don't add servers twice
            $servers = \array_udiff($servers, $resource->getServerList(), [$this, 'compareServers']);
            if ($servers) {
                $resource->addServers($servers);
            }
        } else {
            $resource['servers'] = $servers;
        }

        return $this;
    }

    /**
     * Add servers.
     *
     * @param string       $id
     * @param array|string $servers
     *
     * @return MemcachedResourceManager Provides a fluent interface
     */
    public function addServers($id, $servers)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, ['servers' => $servers]);
        }
        $this->normalizeServers($servers);
        $resource = &$this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            // don't add servers twice
            $servers = \array_udiff($servers, $resource->getServerList(), [$this, 'compareServers']);
            if ($servers) {
                $resource->addServers($servers);
            }
        } else {
            // don't add servers twice
            $resource['servers'] = \array_merge($resource['servers'], \array_udiff($servers, $resource['servers'], [$this, 'compareServers']));
        }

        return $this;
    }

    /**
     * Add one server.
     *
     * @param string       $id
     * @param array|string $server
     *
     * @return MemcachedResourceManager
     */
    public function addServer($id, $server)
    {
        return $this->addServers($id, [$server]);
    }

    /**
     * Normalize one server into the following format:
     * array('host' => <host>, 'port' => <port>, 'weight' => <weight>).
     *
     * @param array|string $server
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeServer(&$server)
    {
        $host = null;
        $port = 11211;
        $weight = 0;
        // convert a single server into an array
        if ($server instanceof \Traversable) {
            $server = ArrayUtils::iteratorToArray($server);
        }
        if (\is_array($server)) {
            // array(<host>[, <port>[, <weight>]])
            if (isset($server[0])) {
                $host = (string) $server[0];
                $port = isset($server[1]) ? (int) $server[1] : $port;
                $weight = isset($server[2]) ? (int) $server[2] : $weight;
            }
            // array('host' => <host>[, 'port' => <port>[, 'weight' => <weight>]])
            if (!isset($server[0]) && isset($server['host'])) {
                $host = (string) $server['host'];
                $port = isset($server['port']) ? (int) $server['port'] : $port;
                $weight = isset($server['weight']) ? (int) $server['weight'] : $weight;
            }
        } else {
            // parse server from URI host{:?port}{?weight}
            $server = \trim($server);
            if (\false === \strpos($server, '://')) {
                $server = 'tcp://'.$server;
            }
            $server = \parse_url($server);
            if (!$server) {
                throw new Exception\InvalidArgumentException('Invalid server given');
            }
            $host = $server['host'];
            $port = isset($server['port']) ? (int) $server['port'] : $port;
            if (isset($server['query'])) {
                $query = null;
                \parse_str($server['query'], $query);
                if (isset($query['weight'])) {
                    $weight = (int) $query['weight'];
                }
            }
        }
        if (!$host) {
            throw new Exception\InvalidArgumentException('Missing required server host');
        }
        $server = ['host' => $host, 'port' => $port, 'weight' => $weight];
    }

    /**
     * Normalize the persistent id.
     *
     * @param string $persistentId
     */
    protected function normalizePersistentId(&$persistentId)
    {
        $persistentId = (string) $persistentId;
    }

    /**
     * Normalize libmemcached options.
     *
     * @param array|\Traversable $libOptions
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeLibOptions(&$libOptions)
    {
        if (!\is_array($libOptions) && !$libOptions instanceof \Traversable) {
            throw new Exception\InvalidArgumentException('Lib-Options must be an array or an instance of Traversable');
        }
        $result = [];
        foreach ($libOptions as $key => $value) {
            $this->normalizeLibOptionKey($key);
            $result[$key] = $value;
        }
        $libOptions = $result;
    }

    /**
     * Convert option name into it's constant value.
     *
     * @param int|string $key
     *
     * @param-out int $key
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeLibOptionKey(&$key)
    {
        // convert option name into it's constant value
        if (\is_string($key)) {
            $const = 'Memcached::OPT_'.\str_replace([' ', '-'], '_', \strtoupper($key));
            if (!\defined($const)) {
                throw new Exception\InvalidArgumentException("Unknown libmemcached option '{$key}' ({$const})");
            }
            $key = \constant($const);
        } else {
            $key = (int) $key;
        }
    }

    /**
     * Normalize a list of servers into the following format:
     * array(array('host' => <host>, 'port' => <port>, 'weight' => <weight>)[, ...]).
     *
     * @param array|string $servers
     */
    protected function normalizeServers(&$servers)
    {
        if (!\is_array($servers) && !$servers instanceof \Traversable) {
            // Convert string into a list of servers
            $servers = \explode(',', $servers);
        }
        $result = [];
        foreach ($servers as $server) {
            $this->normalizeServer($server);
            $result[$server['host'].':'.$server['port']] = $server;
        }
        $servers = \array_values($result);
    }

    /**
     * Compare 2 normalized server arrays
     * (Compares only the host and the port).
     *
     * @return int
     */
    protected function compareServers(array $serverA, array $serverB)
    {
        $keyA = $serverA['host'].':'.$serverA['port'];
        $keyB = $serverB['host'].':'.$serverB['port'];
        if ($keyA === $keyB) {
            return 0;
        }

        return $keyA > $keyB ? 1 : -1;
    }
}
