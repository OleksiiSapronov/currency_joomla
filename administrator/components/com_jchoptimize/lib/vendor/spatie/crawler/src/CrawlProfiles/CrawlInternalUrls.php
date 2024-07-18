<?php

namespace _JchOptimizeVendor\Spatie\Crawler\CrawlProfiles;

use _JchOptimizeVendor\GuzzleHttp\Psr7\Uri;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;

class CrawlInternalUrls extends CrawlProfile
{
    protected $baseUrl;

    public function __construct($baseUrl)
    {
        if (!$baseUrl instanceof UriInterface) {
            $baseUrl = new Uri($baseUrl);
        }
        $this->baseUrl = $baseUrl;
    }

    public function shouldCrawl(UriInterface $url): bool
    {
        return $this->baseUrl->getHost() === $url->getHost();
    }
}
