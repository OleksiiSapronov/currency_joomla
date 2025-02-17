<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter;

use _JchOptimizeVendor\Laminas\Cache\Exception;
use _JchOptimizeVendor\Laminas\Cache\Storage\AvailableSpaceCapableInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\Capabilities;
use _JchOptimizeVendor\Laminas\Cache\Storage\FlushableInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\TotalSpaceCapableInterface;
use Memcached as MemcachedResource;

use function time;

final class Memcached extends AbstractAdapter implements AvailableSpaceCapableInterface, FlushableInterface, TotalSpaceCapableInterface
{
    private const MAXIMUM_KEY_LENGTH = 250;

    /**
     * Has this instance be initialized.
     *
     * @var bool
     */
    private $initialized = \false;

    /**
     * The memcached resource manager.
     *
     * @var null|MemcachedResourceManager
     */
    private $resourceManager;

    /**
     * The memcached resource id.
     *
     * @var null|string
     */
    private $resourceId;

    /**
     * The namespace prefix.
     *
     * @var string
     */
    private $namespacePrefix = '';

    /**
     * @param null|array|MemcachedOptions|\Traversable $options
     *
     * @throws Exception\ExceptionInterface
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        // reset initialized flag on update option(s)
        $initialized = &$this->initialized;
        $this->getEventManager()->attach('option', function () use (&$initialized) {
            $initialized = \false;
        });
    }

    // options
    /**
     * Set options.
     *
     * @see    getOptions()
     *
     * @param array|MemcachedOptions|\Traversable $options
     *
     * @return Memcached
     */
    public function setOptions($options)
    {
        if (!$options instanceof MemcachedOptions) {
            $options = new MemcachedOptions($options);
        }

        return parent::setOptions($options);
    }

    /**
     * Get options.
     *
     * @see setOptions()
     *
     * @return MemcachedOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new MemcachedOptions());
        }

        return $this->options;
    }

    // FlushableInterface
    /**
     * Flush the whole storage.
     *
     * @return bool
     */
    public function flush()
    {
        $memc = $this->getMemcachedResource();
        if (!$memc->flush()) {
            throw $this->getExceptionByResultCode($memc->getResultCode());
        }

        return \true;
    }

    // TotalSpaceCapableInterface
    /**
     * Get total space in bytes.
     *
     * @return float|int
     */
    public function getTotalSpace()
    {
        $memc = $this->getMemcachedResource();
        $stats = $memc->getStats();
        if (\false === $stats) {
            throw new Exception\RuntimeException($memc->getResultMessage(), $memc->getResultCode());
        }
        $mem = \array_pop($stats);

        return $mem['limit_maxbytes'];
    }

    // AvailableSpaceCapableInterface
    /**
     * Get available space in bytes.
     *
     * @return float|int
     */
    public function getAvailableSpace()
    {
        $memc = $this->getMemcachedResource();
        $stats = $memc->getStats();
        if (\false === $stats) {
            throw new Exception\RuntimeException($memc->getResultMessage(), $memc->getResultCode());
        }
        $mem = \array_pop($stats);

        return $mem['limit_maxbytes'] - $mem['bytes'];
    }

    /**
     * Generate exception based of memcached result code.
     *
     * @internal
     *
     * @psalm-param positive-int $code
     *
     * @throws Exception\InvalidArgumentException on success code
     */
    public function getExceptionByResultCode(int $code): Exception\RuntimeException
    {
        switch ($code) {
            case MemcachedResource::RES_SUCCESS:
                throw new Exception\InvalidArgumentException("The result code '{$code}' (SUCCESS) isn't an error");

            default:
                $resource = $this->getMemcachedResource();
                $errorMessage = $resource->getLastErrorMessage();
                \assert(\is_string($errorMessage));

                return new Exception\RuntimeException(\sprintf('%s: %s', $resource->getResultMessage(), $errorMessage), $code);
        }
    }

    // reading
    /**
     * Internal method to get an item.
     *
     * @param string $normalizedKey
     * @param bool   $success
     * @param mixed  $casToken
     *
     * @return mixed Data on success, null on failure
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItem(&$normalizedKey, &$success = null, &$casToken = null)
    {
        $memc = $this->getMemcachedResource();
        $internalKey = $this->namespacePrefix.$normalizedKey;
        if (\func_num_args() > 2) {
            if (\defined('Memcached::GET_EXTENDED')) {
                $output = $memc->get($internalKey, null, MemcachedResource::GET_EXTENDED);
                $casToken = $output ? $output['cas'] : $casToken;
                $result = $output ? $output['value'] : \false;
            } else {
                $result = $memc->get($internalKey, null, $casToken);
            }
        } else {
            $result = $memc->get($internalKey);
        }
        $success = \true;
        if (\false === $result) {
            $rsCode = $memc->getResultCode();
            if (MemcachedResource::RES_NOTFOUND === $rsCode) {
                $result = null;
                $success = \false;
            } elseif ($rsCode) {
                $success = \false;

                throw $this->getExceptionByResultCode($rsCode);
            }
        }

        return $result;
    }

    /**
     * Internal method to get multiple items.
     *
     * @return array Associative array of keys and values
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItems(array &$normalizedKeys)
    {
        $memc = $this->getMemcachedResource();
        foreach ($normalizedKeys as &$normalizedKey) {
            $normalizedKey = $this->namespacePrefix.$normalizedKey;
        }
        $result = $memc->getMulti($normalizedKeys);
        if (\false === $result) {
            throw $this->getExceptionByResultCode($memc->getResultCode());
        }
        // if $result is empty the loop below can be avouded
        // and HHVM returns NULL instead of an empty array in this case
        if (empty($result)) {
            return [];
        }
        // remove namespace prefix from result
        if ('' !== $this->namespacePrefix) {
            $tmp = [];
            $nsPrefixLength = \strlen($this->namespacePrefix);
            foreach ($result as $internalKey => $value) {
                $tmp[\substr($internalKey, $nsPrefixLength)] = $value;
            }
            $result = $tmp;
        }

        return $result;
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param string $normalizedKey
     *
     * @return bool
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItem(&$normalizedKey)
    {
        $memc = $this->getMemcachedResource();
        $value = $memc->get($this->namespacePrefix.$normalizedKey);
        if (\false === $value) {
            $rsCode = $memc->getResultCode();
            if (MemcachedResource::RES_SUCCESS === $rsCode) {
                return \true;
            }
            if (MemcachedResource::RES_NOTFOUND === $rsCode) {
                return \false;
            }

            throw $this->getExceptionByResultCode($rsCode);
        }

        return \true;
    }

    /**
     * Internal method to test multiple items.
     *
     * @return array Array of found keys
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItems(array &$normalizedKeys)
    {
        return \array_keys($this->internalGetItems($normalizedKeys));
    }

    /**
     * Get metadata of multiple items.
     *
     * @return array Associative array of keys and metadata
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadatas(array &$normalizedKeys)
    {
        return \array_fill_keys(\array_keys($this->internalGetItems($normalizedKeys)), []);
    }

    // writing
    /**
     * Internal method to store an item.
     *
     * @param string $normalizedKey
     * @param mixed  $value
     *
     * @return bool
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItem(&$normalizedKey, &$value)
    {
        $memc = $this->getMemcachedResource();
        $expiration = $this->expirationTime();
        if (!$memc->set($this->namespacePrefix.$normalizedKey, $value, $expiration)) {
            throw $this->getExceptionByResultCode($memc->getResultCode());
        }

        return \true;
    }

    /**
     * Internal method to store multiple items.
     *
     * @return array Array of not stored keys
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItems(array &$normalizedKeyValuePairs)
    {
        $memc = $this->getMemcachedResource();
        $expiration = $this->expirationTime();
        $namespacedKeyValuePairs = [];
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            $namespacedKeyValuePairs[$this->namespacePrefix.$normalizedKey] = $value;
        }
        if (!$memc->setMulti($namespacedKeyValuePairs, $expiration)) {
            throw $this->getExceptionByResultCode($memc->getResultCode());
        }

        return [];
    }

    /**
     * Add an item.
     *
     * @param string $normalizedKey
     * @param mixed  $value
     *
     * @return bool
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalAddItem(&$normalizedKey, &$value)
    {
        $memc = $this->getMemcachedResource();
        $expiration = $this->expirationTime();
        if (!$memc->add($this->namespacePrefix.$normalizedKey, $value, $expiration)) {
            if (MemcachedResource::RES_NOTSTORED === $memc->getResultCode()) {
                return \false;
            }

            throw $this->getExceptionByResultCode($memc->getResultCode());
        }

        return \true;
    }

    /**
     * Internal method to replace an existing item.
     *
     * @param string $normalizedKey
     * @param mixed  $value
     *
     * @return bool
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalReplaceItem(&$normalizedKey, &$value)
    {
        $memc = $this->getMemcachedResource();
        $expiration = $this->expirationTime();
        if (!$memc->replace($this->namespacePrefix.$normalizedKey, $value, $expiration)) {
            $rsCode = $memc->getResultCode();
            if (MemcachedResource::RES_NOTSTORED === $rsCode) {
                return \false;
            }

            throw $this->getExceptionByResultCode($rsCode);
        }

        return \true;
    }

    /**
     * Internal method to set an item only if token matches.
     *
     * @see    getItem()
     * @see    setItem()
     *
     * @param mixed  $token
     * @param string $normalizedKey
     * @param mixed  $value
     *
     * @return bool
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalCheckAndSetItem(&$token, &$normalizedKey, &$value)
    {
        $memc = $this->getMemcachedResource();
        $expiration = $this->expirationTime();
        $result = $memc->cas($token, $this->namespacePrefix.$normalizedKey, $value, $expiration);
        if (\false === $result) {
            $rsCode = $memc->getResultCode();
            if (0 !== $rsCode && MemcachedResource::RES_DATA_EXISTS !== $rsCode) {
                throw $this->getExceptionByResultCode($rsCode);
            }
        }

        return $result;
    }

    /**
     * Internal method to remove an item.
     *
     * @param string $normalizedKey
     *
     * @return bool
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItem(&$normalizedKey)
    {
        $memc = $this->getMemcachedResource();
        $result = $memc->delete($this->namespacePrefix.$normalizedKey);
        if (\false === $result) {
            $rsCode = $memc->getResultCode();
            if (MemcachedResource::RES_NOTFOUND === $rsCode) {
                return \false;
            }
            if (MemcachedResource::RES_SUCCESS !== $rsCode) {
                throw $this->getExceptionByResultCode($rsCode);
            }
        }

        return \true;
    }

    /**
     * Internal method to remove multiple items.
     *
     * @return array Array of not removed keys
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItems(array &$normalizedKeys)
    {
        $memc = $this->getMemcachedResource();
        // support for removing multiple items at once has been added in ext/memcached-2.0.0
        // and HHVM doesn't support this feature yet
        if (!\method_exists($memc, 'deleteMulti')) {
            return parent::internalRemoveItems($normalizedKeys);
        }
        foreach ($normalizedKeys as &$normalizedKey) {
            $normalizedKey = $this->namespacePrefix.$normalizedKey;
        }
        $missingKeys = [];
        foreach ($memc->deleteMulti($normalizedKeys) as $normalizedKey => $rsCode) {
            if (\true !== $rsCode && MemcachedResource::RES_SUCCESS !== $rsCode) {
                if (MemcachedResource::RES_NOTFOUND !== $rsCode) {
                    throw $this->getExceptionByResultCode($rsCode);
                }
                $missingKeys[] = $normalizedKey;
            }
        }
        // remove namespace prefix
        if ($missingKeys && '' !== $this->namespacePrefix) {
            $nsPrefixLength = \strlen($this->namespacePrefix);
            foreach ($missingKeys as &$missingKey) {
                $missingKey = \substr($missingKey, $nsPrefixLength);
            }
        }

        return $missingKeys;
    }

    /**
     * Internal method to increment an item.
     *
     * @param string $normalizedKey
     * @param int    $value
     *
     * @return bool|int The new value on success, false on failure
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalIncrementItem(&$normalizedKey, &$value)
    {
        $memc = $this->getMemcachedResource();
        $internalKey = $this->namespacePrefix.$normalizedKey;
        $value = (int) $value;
        $newValue = $memc->increment($internalKey, $value);
        if (\false === $newValue) {
            $rsCode = $memc->getResultCode();
            // initial value
            if (MemcachedResource::RES_NOTFOUND === $rsCode) {
                $newValue = $value;
                $memc->add($internalKey, $newValue, $this->expirationTime());
                $rsCode = $memc->getResultCode();
            }
            if ($rsCode) {
                throw $this->getExceptionByResultCode($rsCode);
            }
        }

        return $newValue;
    }

    /**
     * Internal method to decrement an item.
     *
     * @param string $normalizedKey
     * @param int    $value
     *
     * @return bool|int The new value on success, false on failure
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalDecrementItem(&$normalizedKey, &$value)
    {
        $memc = $this->getMemcachedResource();
        $internalKey = $this->namespacePrefix.$normalizedKey;
        $value = (int) $value;
        $newValue = $memc->decrement($internalKey, $value);
        if (\false === $newValue) {
            $rsCode = $memc->getResultCode();
            // initial value
            if (MemcachedResource::RES_NOTFOUND === $rsCode) {
                $newValue = -$value;
                $memc->add($internalKey, $newValue, $this->expirationTime());
                $rsCode = $memc->getResultCode();
            }
            if ($rsCode) {
                throw $this->getExceptionByResultCode($rsCode);
            }
        }

        return $newValue;
    }

    // status
    /**
     * Internal method to get capabilities of this adapter.
     *
     * @return Capabilities
     */
    protected function internalGetCapabilities()
    {
        if (!$this->initialized) {
            $this->initialize();
        }
        $keyLengthReservedForNamespaceWithPrefix = \strlen($this->namespacePrefix);
        $maximumKeyLength = self::MAXIMUM_KEY_LENGTH - $keyLengthReservedForNamespaceWithPrefix;
        if (null === $this->capabilities) {
            $this->capabilityMarker = new \stdClass();
            $this->capabilities = new Capabilities($this, $this->capabilityMarker, ['supportedDatatypes' => ['NULL' => \true, 'boolean' => \true, 'integer' => \true, 'double' => \true, 'string' => \true, 'array' => \true, 'object' => 'object', 'resource' => \false], 'supportedMetadata' => [], 'minTtl' => 1, 'maxTtl' => 0, 'staticTtl' => \true, 'ttlPrecision' => 1, 'useRequestTime' => \false, 'maxKeyLength' => $maximumKeyLength, 'namespaceIsPrefix' => \true]);
        }

        return $this->capabilities;
    }

    /**
     * Initialize the internal memcached resource.
     *
     * @return MemcachedResource
     */
    private function getMemcachedResource()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->resourceManager->getResource($this->resourceId);
    }

    // internal
    /**
     * Get expiration time by ttl.
     *
     * Some storage commands involve sending an expiration value (relative to
     * an item or to an operation requested by the client) to the server. In
     * all such cases, the actual value sent may either be Unix time (number of
     * seconds since January 1, 1970, as an integer), or a number of seconds
     * starting from current time. In the latter case, this number of seconds
     * may not exceed 60*60*24*30 (number of seconds in 30 days); if the
     * expiration value is larger than that, the server will consider it to be
     * real Unix time value rather than an offset from current time.
     */
    private function expirationTime(): int
    {
        $ttl = $this->getOptions()->getTtl();
        if ($ttl > 2592000) {
            return \time() + $ttl;
        }

        return (int) $ttl;
    }

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }
        $options = $this->getOptions();
        // get resource manager and resource id
        $this->resourceManager = $options->getResourceManager();
        $this->resourceId = $options->getResourceId();
        // init namespace prefix
        $namespace = $options->getNamespace();
        if ('' !== $namespace) {
            $this->namespacePrefix = $namespace.$options->getNamespaceSeparator();
        } else {
            $this->namespacePrefix = '';
        }
        // update initialized flag
        $this->initialized = \true;
    }
}
