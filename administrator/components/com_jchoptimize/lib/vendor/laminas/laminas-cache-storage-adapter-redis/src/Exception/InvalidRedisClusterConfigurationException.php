<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Exception;

use _JchOptimizeVendor\Laminas\Cache\Exception\InvalidArgumentException;

final class InvalidRedisClusterConfigurationException extends InvalidArgumentException
{
    public static function fromMissingSeedsConfiguration(): self
    {
        return new self('Could not find `redis.clusters.seeds` entry in the php.ini file(s).');
    }

    public static function fromMissingSeedsForNamedConfiguration(string $name): self
    {
        return new self(\sprintf('Missing `%s` within the configured `redis.cluster.seeds` entry in the php.ini file(s).', $name));
    }

    public static function fromMissingRequiredValues(): self
    {
        return new self('Missing either `name` or `seeds`.');
    }

    public static function fromNameAndSeedsProvidedViaConfiguration(): self
    {
        return new self('Please provide either `name` or `seeds` configuration, not both.');
    }

    public static function fromInvalidSeedsConfiguration(string $seed): self
    {
        return new self(\sprintf('Configured `seed` %s is invalid. Must be configured as "host:port" while separated by a colon.', $seed));
    }
}
