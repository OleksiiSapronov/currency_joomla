<?php

namespace _JchOptimizeVendor\Laminas\Cache\Pattern;

use _JchOptimizeVendor\Laminas\Cache\Exception;

class OutputCache extends AbstractStorageCapablePattern
{
    /**
     * The key stack.
     *
     * @var array
     */
    protected $keyStack = [];

    /**
     * if there is a cached item with the given key display it's data and return true
     * else start buffering output until end() is called or the script ends.
     *
     * @param string $key Key
     *
     * @return bool
     *
     * @throws Exception\MissingKeyException if key is missing
     */
    public function start($key)
    {
        if (($key = (string) $key) === '') {
            throw new Exception\MissingKeyException('Missing key to read/write output from cache');
        }
        $success = null;
        $storage = $this->getStorage();
        $data = $storage->getItem($key, $success);
        if ($success) {
            echo $data;

            return \true;
        }
        \ob_start();
        \ob_implicit_flush(0);
        $this->keyStack[] = $key;

        return \false;
    }

    /**
     * Stops buffering output, write buffered data to cache using the given key on start()
     * and displays the buffer.
     *
     * @return bool TRUE on success, FALSE on failure writing to cache
     *
     * @throws Exception\RuntimeException if output cache not started or buffering not active
     */
    public function end()
    {
        $key = \array_pop($this->keyStack);
        if (null === $key) {
            throw new Exception\RuntimeException('Output cache not started');
        }
        $output = \ob_get_flush();
        if (\false === $output) {
            throw new Exception\RuntimeException('Output buffering not active');
        }
        $storage = $this->getStorage();

        return $storage->setItem($key, $output);
    }
}
