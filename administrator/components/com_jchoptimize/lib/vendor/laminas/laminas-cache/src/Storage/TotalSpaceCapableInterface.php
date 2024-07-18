<?php

namespace _JchOptimizeVendor\Laminas\Cache\Storage;

interface TotalSpaceCapableInterface
{
    /**
     * Get total space in bytes.
     *
     * @return float|int
     */
    public function getTotalSpace();
}
