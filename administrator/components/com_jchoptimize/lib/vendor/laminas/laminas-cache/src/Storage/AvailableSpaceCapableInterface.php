<?php

namespace _JchOptimizeVendor\Laminas\Cache\Storage;

interface AvailableSpaceCapableInterface
{
    /**
     * Get available space in bytes.
     *
     * @return float|int
     */
    public function getAvailableSpace();
}
