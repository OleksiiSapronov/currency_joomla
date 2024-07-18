<?php

/**
 * @see https://github.com/laminas/laminas-serializer for the canonical source repository
 */
declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Serializer\Adapter;

use _JchOptimizeVendor\Laminas\Serializer\Exception;
use _JchOptimizeVendor\Laminas\Stdlib\ErrorHandler;

class MsgPack extends AbstractAdapter
{
    /** @var string Serialized 0 value */
    private static $serialized0;

    /**
     * Constructor.
     *
     * @param AdapterOptions|array|\Traversable $options
     *
     * @throws Exception\ExtensionNotLoadedException if msgpack extension is not present
     */
    public function __construct($options = null)
    {
        if (!\extension_loaded('msgpack')) {
            throw new Exception\ExtensionNotLoadedException('PHP extension "msgpack" is required for this adapter');
        }
        if (null === static::$serialized0) {
            static::$serialized0 = \msgpack_serialize(0);
        }
        parent::__construct($options);
    }

    /**
     * Serialize PHP value to msgpack.
     *
     * @param mixed $value
     *
     * @return string
     *
     * @throws Exception\RuntimeException on msgpack error
     */
    public function serialize($value)
    {
        ErrorHandler::start();
        $ret = \msgpack_serialize($value);
        $err = ErrorHandler::stop();
        if (\false === $ret) {
            throw new Exception\RuntimeException('Serialization failed', 0, $err);
        }

        return $ret;
    }

    /**
     * Deserialize msgpack string to PHP value.
     *
     * @param string $serialized
     *
     * @return mixed
     *
     * @throws Exception\RuntimeException on msgpack error
     */
    public function unserialize($serialized)
    {
        if ($serialized === static::$serialized0) {
            return 0;
        }
        ErrorHandler::start();
        $ret = \msgpack_unserialize($serialized);
        $err = ErrorHandler::stop();
        if (0 === $ret) {
            throw new Exception\RuntimeException('Unserialization failed', 0, $err);
        }

        return $ret;
    }
}
