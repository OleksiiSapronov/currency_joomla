<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib\Guard;

use _JchOptimizeVendor\Laminas\Stdlib\Exception\InvalidArgumentException;
use Exception;

/**
 * Provide a guard method against null data.
 */
trait NullGuardTrait
{
    /**
     * Verify that the data is not null.
     *
     * @param mixed  $data           the data to verify
     * @param string $dataName       the data name
     * @param string $exceptionClass FQCN for the exception
     *
     * @throws \Exception
     */
    protected function guardAgainstNull($data, $dataName = 'Argument', $exceptionClass = InvalidArgumentException::class)
    {
        if (null === $data) {
            $message = \sprintf('%s cannot be null', $dataName);

            throw new $exceptionClass($message);
        }
    }
}
