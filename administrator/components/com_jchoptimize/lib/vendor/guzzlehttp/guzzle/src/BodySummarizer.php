<?php

namespace _JchOptimizeVendor\GuzzleHttp;

use _JchOptimizeVendor\Psr\Http\Message\MessageInterface;

final class BodySummarizer implements BodySummarizerInterface
{
    /**
     * @var null|int
     */
    private $truncateAt;

    public function __construct(int $truncateAt = null)
    {
        $this->truncateAt = $truncateAt;
    }

    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message): ?string
    {
        return null === $this->truncateAt ? \_JchOptimizeVendor\GuzzleHttp\Psr7\Message::bodySummary($message) : \_JchOptimizeVendor\GuzzleHttp\Psr7\Message::bodySummary($message, $this->truncateAt);
    }
}
