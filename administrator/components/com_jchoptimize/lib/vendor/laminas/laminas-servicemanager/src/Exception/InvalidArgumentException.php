<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\ServiceManager\Exception;

use _JchOptimizeVendor\Laminas\ServiceManager\AbstractFactoryInterface;
use _JchOptimizeVendor\Laminas\ServiceManager\Initializer\InitializerInterface;
use InvalidArgumentException as SplInvalidArgumentException;

class InvalidArgumentException extends SplInvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $initializer
     */
    public static function fromInvalidInitializer($initializer): self
    {
        return new self(\sprintf('An invalid initializer was registered. Expected a callable or an instance of "%s"; received "%s"', InitializerInterface::class, \is_object($initializer) ? \get_class($initializer) : \gettype($initializer)));
    }

    /**
     * @param mixed $abstractFactory
     */
    public static function fromInvalidAbstractFactory($abstractFactory): self
    {
        return new self(\sprintf('An invalid abstract factory was registered. Expected an instance of or a valid class name resolving to an implementation of "%s", but "%s" was received.', AbstractFactoryInterface::class, \is_object($abstractFactory) ? \get_class($abstractFactory) : \gettype($abstractFactory)));
    }
}
