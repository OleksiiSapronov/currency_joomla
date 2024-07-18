<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib\Guard;

use _JchOptimizeVendor\Laminas\Stdlib\Exception\InvalidArgumentException;
use Exception;

/**
 * Provide a guard method against empty data.
 */
trait EmptyGuardTrait
{
    /**
     * Verify that the data is not empty.
     *
     * @param mixed  $data           the data to verify
     * @param string $dataName       the data name
     * @param string $exceptionClass FQCN for the exception
     *
     * @throws \Exception
     */
    protected function guardAgainstEmpty($data, $dataName = 'Argument', $exceptionClass = InvalidArgumentException::class)
    {
        if (empty($data)) {
            $message = \sprintf('%s cannot be empty', $dataName);

            throw new $exceptionClass($message);
        }
    }
}
