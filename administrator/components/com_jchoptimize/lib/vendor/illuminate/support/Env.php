<?php

namespace _JchOptimizeVendor\Illuminate\Support;

use _JchOptimizeVendor\Dotenv\Repository\Adapter\PutenvAdapter;
use _JchOptimizeVendor\Dotenv\Repository\RepositoryBuilder;
use _JchOptimizeVendor\PhpOption\Option;

class Env
{
    /**
     * Indicates if the putenv adapter is enabled.
     *
     * @var bool
     */
    protected static $putenv = \true;

    /**
     * The environment repository instance.
     *
     * @var null|\Dotenv\Repository\RepositoryInterface
     */
    protected static $repository;

    /**
     * Enable the putenv adapter.
     */
    public static function enablePutenv()
    {
        static::$putenv = \true;
        static::$repository = null;
    }

    /**
     * Disable the putenv adapter.
     */
    public static function disablePutenv()
    {
        static::$putenv = \false;
        static::$repository = null;
    }

    /**
     * Get the environment repository instance.
     *
     * @return \Dotenv\Repository\RepositoryInterface
     */
    public static function getRepository()
    {
        if (null === static::$repository) {
            $builder = RepositoryBuilder::createWithDefaultAdapters();
            if (static::$putenv) {
                $builder = $builder->addAdapter(PutenvAdapter::class);
            }
            static::$repository = $builder->immutable()->make();
        }

        return static::$repository;
    }

    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Option::fromValue(static::getRepository()->get($key))->map(function ($value) {
            switch (\strtolower($value)) {
                case 'true':
                case '(true)':
                    return \true;

                case 'false':
                case '(false)':
                    return \false;

                case 'empty':
                case '(empty)':
                    return '';

                case 'null':
                case '(null)':
                    return;
            }
            if (\preg_match('/\\A([\'"])(.*)\\1\\z/', $value, $matches)) {
                return $matches[2];
            }

            return $value;
        })->getOrCall(function () use ($default) {
            return value($default);
        });
    }
}
