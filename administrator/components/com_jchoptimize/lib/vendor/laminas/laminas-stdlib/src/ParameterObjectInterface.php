<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

interface ParameterObjectInterface
{
    /**
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key);

    /**
     * @param string $key
     */
    public function __unset($key);
}
