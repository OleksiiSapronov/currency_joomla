<?php

namespace _JchOptimizeVendor\Illuminate\Bus\Events;

use _JchOptimizeVendor\Illuminate\Bus\Batch;

class BatchDispatched
{
    /**
     * The batch instance.
     *
     * @var \Illuminate\Bus\Batch
     */
    public $batch;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Bus\Batch $batch
     */
    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }
}
