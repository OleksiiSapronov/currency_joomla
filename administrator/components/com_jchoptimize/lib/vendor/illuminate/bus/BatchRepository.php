<?php

namespace _JchOptimizeVendor\Illuminate\Bus;

use Closure;

interface BatchRepository
{
    /**
     * Retrieve a list of batches.
     *
     * @param int   $limit
     * @param mixed $before
     *
     * @return \Illuminate\Bus\Batch[]
     */
    public function get($limit, $before);

    /**
     * Retrieve information about an existing batch.
     *
     * @return null|\Illuminate\Bus\Batch
     */
    public function find(string $batchId);

    /**
     * Store a new pending batch.
     *
     * @param \Illuminate\Bus\PendingBatch $batch
     *
     * @return \Illuminate\Bus\Batch
     */
    public function store(PendingBatch $batch);

    /**
     * Increment the total number of jobs within the batch.
     */
    public function incrementTotalJobs(string $batchId, int $amount);

    /**
     * Decrement the total number of pending jobs for the batch.
     *
     * @return \Illuminate\Bus\UpdatedBatchJobCounts
     */
    public function decrementPendingJobs(string $batchId, string $jobId);

    /**
     * Increment the total number of failed jobs for the batch.
     *
     * @return \Illuminate\Bus\UpdatedBatchJobCounts
     */
    public function incrementFailedJobs(string $batchId, string $jobId);

    /**
     * Mark the batch that has the given ID as finished.
     */
    public function markAsFinished(string $batchId);

    /**
     * Cancel the batch that has the given ID.
     */
    public function cancel(string $batchId);

    /**
     * Delete the batch that has the given ID.
     */
    public function delete(string $batchId);

    /**
     * Execute the given Closure within a storage specific transaction.
     *
     * @return mixed
     */
    public function transaction(\Closure $callback);
}
