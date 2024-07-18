<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

/**
 * Interface to allow objects to have initialization logic.
 */
interface InitializableInterface
{
    /**
     * Init an object.
     */
    public function init();
}
