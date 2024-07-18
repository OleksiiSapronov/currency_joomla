<?php

/**
 * @see https://github.com/laminas/laminas-serializer for the canonical source repository
 */
declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Serializer\Adapter;

use _JchOptimizeVendor\Laminas\Serializer\Exception;

class PhpSerializeOptions extends AdapterOptions
{
    /**
     * The list of allowed classes for unserialization (PHP 7.0+).
     *
     * Possible values:
     *
     * - `array` of class names that are allowed to be unserialized
     * - `true` if all classes should be allowed (behavior pre-PHP 7.0)
     * - `false` if no classes should be allowed
     *
     * @var bool|string[]
     */
    protected $unserializeClassWhitelist = \true;

    /**
     * @param bool|string[] $unserializeClassWhitelist
     */
    public function setUnserializeClassWhitelist($unserializeClassWhitelist)
    {
        if (\true !== $unserializeClassWhitelist && \PHP_MAJOR_VERSION < 7) {
            throw new Exception\InvalidArgumentException('Class whitelist for unserialize() is only available on PHP versions 7.0 or higher.');
        }
        $this->unserializeClassWhitelist = $unserializeClassWhitelist;
    }

    /**
     * @return bool|string[]
     */
    public function getUnserializeClassWhitelist()
    {
        return $this->unserializeClassWhitelist;
    }
}
