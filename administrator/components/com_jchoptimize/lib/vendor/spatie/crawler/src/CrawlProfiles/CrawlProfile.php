<?php

namespace _JchOptimizeVendor\Spatie\Crawler\CrawlProfiles;

use _JchOptimizeVendor\Psr\Http\Message\UriInterface;

abstract class CrawlProfile
{
    /**
     * Determine if the given url should be crawled.
     *
     * @param \Psr\Http\Message\UriInterface $url
     */
    abstract public function shouldCrawl(UriInterface $url): bool;
}
