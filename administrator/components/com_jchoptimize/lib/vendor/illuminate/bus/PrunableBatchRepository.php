<?php

namespace _JchOptimizeVendor\Illuminate\Bus;

interface PrunableBatchRepository extends BatchRepository
{
    /**
     * Prune all of the entries older than the given date.
     *
     * @return int
     */
    public function prune(\DateTimeInterface $before);
}
