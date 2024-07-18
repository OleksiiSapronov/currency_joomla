<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\ServiceManager\Exception;

class ContainerModificationsNotAllowedException extends \DomainException implements ExceptionInterface
{
    /**
     * @param string $service name of service that already exists
     */
    public static function fromExistingService(string $service): self
    {
        return new self(\sprintf('The container does not allow replacing or updating a service with existing instances; the following service already exists in the container: %s', $service));
    }
}
